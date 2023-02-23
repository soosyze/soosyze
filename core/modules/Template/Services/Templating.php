<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Template\Services;

use Core;
use Soosyze\Components\Http\Stream;
use Soosyze\Components\Util\Util;
use Soosyze\Config;
use Soosyze\ResponseEmitter;

class Templating extends \Soosyze\Components\Http\Response
{
    public const THEME_PUBLIC = 'theme';

    public const THEME_ADMIN = 'theme_admin';

    /**
     * @var array
     */
    private static $scriptsGlobal = [];

    /**
     * @var array
     */
    private static $stylesGlobal = [];

    /**
     * @var Block|null
     */
    private $template;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $configJs = [];

    /**
     * @var Core
     */
    private $core;

    /**
     * Nom du theme utilisé par défaut.
     *
     * @var string
     */
    private $defaultThemeName = '';

    /**
     * Chemin du thème.
     *
     * @var string
     */
    private $defaultThemePath = '';

    /**
     * Liste des répertoires contenant les thèmes.
     *
     * @var string[]
     */
    private $themesPath = [];

    /**
     * Liste des scripts JS.
     *
     * @var array
     */
    private $scripts = [];

    /**
     * Liste des styles CSS.
     *
     * @var array
     */
    private $styles = [];

    /**
     * Les données du fichier composer.json
     *
     * @var array
     */
    private $composer = [];

    /**
     * Les méta données de la page.
     *
     * @var array
     */
    private $meta = [];

    /**
     * @var bool
     */
    private $isDarkTheme = false;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var string
     */
    private $basePath;

    public function __construct(Core $core, Config $config)
    {
        parent::__construct();

        $this->core       = $core;
        $this->config     = $config;
        $this->themesPath = is_array($core->getSetting('themes_path'))
            ? $core->getSetting('themes_path')
            : [];
        $this->basePath   = $core->getRequest()->getBasePath();
        $this->pathViews  = dirname(__DIR__) . '/Views/';

        $this->loadAssets();
    }

    public function __toString(): string
    {
        $scriptsInline = $this->getBlock('this')->getVar('scripts_inline');
        $this->view('this', [
            'meta'          => $this->makeBalise('meta', $this->meta),
            'script_inline' => $this->makeConfigJs() . $scriptsInline,
            'styles'        => $this->makeBalise('link', (self::$stylesGlobal + $this->styles)),
            'scripts'       => $this->makeBalise('script', (self::$scriptsGlobal + $this->scripts), false) . $this->makeMessages()
        ]);

        $content    = $this->getThemplate()->render();
        $this->body = new Stream($content);

        return (new ResponseEmitter)->emit($this);
    }

    public function init(): void
    {
        $this->loadComposer();

        $submenu = $this->createBlock('submenu.php', $this->pathViews);

        $page = $this->createBlock('page.php', $this->pathViews)
            ->addVars([
                'title'      => '',
                'title_main' => '',
                'icon'       => '',
                'logo'       => ''
            ])
            ->addVars($this->core->getSettings())
            ->addBlock('content')
            ->addBlock('submenu', $submenu)
            ->addBlock('main_menu')
            ->addBlock('second_menu');

        $this->template = $this->createBlock('html.php', $this->pathViews)
            ->addBlock('page', $page)
            ->addVars([
                'dark'        => $this->isDarkTheme ? 'dark' : '',
                'title'       => '',
                'logo'        => '',
                'favicon'     => '',
                'description' => '',
                'keyboard'    => '',
                'scripts_inline' => ''
            ])
            ->addVars($this->core->getSettings());
    }

    public function getTheme(string $theme = self::THEME_PUBLIC): self
    {
        $granted = $this->core->callHook('app.granted', [ 'template.admin' ]);

        if ($theme === self::THEME_ADMIN && $granted) {
            $this->defaultThemeName = self::THEME_ADMIN;
            $this->isDarkTheme      = (bool) $this->config->get('settings.theme_admin_dark');
        } else {
            $this->defaultThemeName = self::THEME_PUBLIC;
        }

        foreach ($this->themesPath as $path) {
            $dir = $path . '/' . $this->config->get('settings.' . $this->defaultThemeName, '');
            if (is_dir($dir)) {
                $this->defaultThemePath = $dir;

                break;
            }
        }
        $this->init();

        return $this;
    }

    public function isTheme(string $themeName): bool
    {
        return $this->defaultThemeName === $themeName;
    }

    /**
     * Ajoute des variables à la template courante ou à une sous template.
     *
     * @return $this
     */
    public function view(string $parent, array $vars): self
    {
        $this->getBlock($parent)->addVars($vars);

        return $this;
    }

    /**
     * Ajoute un bloc à la template courante ou une sous template.
     * Ce bloc peut recevoir des variables fournit en dernier paramètre de la fonction.
     */
    public function make(
        string $selector,
        string $tpl,
        string $tplPath,
        array $vars = []
    ): self {
        $template = $this->createBlock($tpl, $tplPath);
        $this->addBlock($selector, $template, $vars);

        return $this;
    }

    public function addFilterVar(
        string $selector,
        string $key,
        callable $function
    ): self {
        $this->getBlock($selector)->addFilterVar($key, $function);

        return $this;
    }

    public function addFilterBlock(
        string $selector,
        string $key,
        callable $function
    ): self {
        $this->getBlock($selector)->addFilterBlock($key, $function);

        return $this;
    }

    public function addFilterOutput(string $selector, callable $function): self
    {
        $this->getBlock($selector)->addFilterOutput($function);

        return $this;
    }

    public function override(string $selector, array $templates): self
    {
        $this->getBlock($selector)->setNamesOverride($templates);

        return $this;
    }

    public function getBlock(string $selector): Block
    {
        return $this->getThemplate()->getBlockWithParent($selector);
    }

    public function createBlock(string $tpl, string $tplPath): Block
    {
        return (new Block($tpl, $tplPath))
                ->addVars([
                    'base_path'  => $this->basePath,
                    'base_theme' => $this->basePath . $this->defaultThemePath . '/'
                ])
                ->addPathOverride($this->getPathTheme());
    }

    public function addBlock(string $selector, ?Block $block, array $vars = []): self
    {
        if ($block !== null) {
            $block->addVars($vars);
        }

        sscanf($selector, '%[a-z].%s', $parent, $child);

        if ($child) {
            $this->getBlock((string) $parent)->addBlock((string) $child, $block);
        } else {
            $this->getThemplate()->addBlock((string) $parent, $block);
        }

        return $this;
    }

    public function getPathTheme(): string
    {
        return is_dir(ROOT . $this->defaultThemePath)
            ? ROOT . $this->defaultThemePath . '/'
            : $this->defaultThemePath;
    }

    /**
     * @phpstan-return array<string>
     */
    public function getSections(): array
    {
        if (!$this->composer) {
            $this->loadComposer();
        }

        return $this->composer[ 'extra' ][ 'soosyze' ][ 'sections' ] ?? [];
    }

    public function loadComposer(): void
    {
        $pathTheme = $this->getPathTheme();
        if (is_file($pathTheme . 'composer.json')) {
            $this->composer = (array) Util::getJson($pathTheme . 'composer.json');
        }
    }

    public function addMetas(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    public function addMeta(array $meta): self
    {
        $this->meta[] = $meta;

        return $this;
    }

    public function addScript(string $name, string $href, array $attr = []): self
    {
        $this->scripts[ $name ] = [ 'src' => $href, 'type' => 'text/javascript' ] + $attr;

        return $this;
    }

    public function addStyle(string $name, string $src, array $attr = []): self
    {
        $this->styles[ $name ] = [ 'href' => $src, 'rel' => 'stylesheet' ] + $attr;

        return $this;
    }

    public function addConfigJs(string $name, array $value): self
    {
        $this->configJs[ $name ] = $value;

        return $this;
    }

    public static function setStylesGlobal(array $styles): void
    {
        self::$stylesGlobal = $styles;
    }

    public static function setScriptsGlobal(array $scritps): void
    {
        self::$scriptsGlobal = $scritps;
    }

    private function loadAssets(): void
    {
        $vendor = $this->core->getPath('modules', 'modules/core', false) . '/Template/Assets';

        $this->scripts = [
            'core' => [
                'src' => "$vendor/js/script.js"
            ]
        ];
    }

    private function makeBalise(string $type, array $data, bool $orphan = true): string
    {
        $out = '';
        foreach ($data as $attrs) {
            $out .= $orphan
                ? sprintf('<%s%s/>', $type, $this->renderAttrInput($attrs)) . PHP_EOL
                : sprintf('<%s%s></%s>', $type, $this->renderAttrInput($attrs), $type) . PHP_EOL;
        }

        return $out;
    }

    private function makeMessages(): string
    {
        $out = '';
        if (isset($_SESSION[ 'messages' ])) {
            $out = sprintf(
                '<script>(function () {renderMessage(%s);})();</script>',
                json_encode([ 'messages' => $_SESSION[ 'messages' ] ])
            ) . PHP_EOL;
            unset($_SESSION[ 'messages' ]);
        }

        return $out;
    }

    private function makeConfigJs(): string
    {
        return sprintf('<script>var config =%s</script>', json_encode($this->configJs)) . PHP_EOL;
    }

    private function renderAttrInput(array $attr): string
    {
        $html = '';
        foreach ($attr as $key => $value) {
            $html .= sprintf(' %s="%s"', htmlspecialchars($key), htmlentities($value));
        }

        return $html;
    }

    private function getThemplate(): Block
    {
        if ($this->template !== null) {
            return $this->template;
        }

        $this->getTheme();

        if ($this->template === null) {
            throw new \Exception('The template must initialize.');
        }

        return $this->template;
    }
}
