<?php

namespace SoosyzeCore\Template\Services;

use Soosyze\Components\Http\Stream;
use Soosyze\Components\Util\Util;

class Templating extends \Soosyze\Components\Http\Response
{
    const THEME_PUBLIC = 'theme';

    const THEME_ADMIN = 'theme_admin';

    /**
     * @var type
     */
    private static $scriptsGlobal = [];

    /**
     * @var type
     */
    private static $stylesGlobal = [];

    /**
     * @var Block
     */
    private $template;

    /**
     * @var \Soosyze\Config
     */
    private $config;

    /**
     * @var array
     */
    private $configJs = [];

    /**
     * @var \Soosyze\App
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

    private $isDarkTheme = false;

    public function __construct($core, $config)
    {
        parent::__construct();

        $this->core        = $core;
        $this->config      = $config;
        $this->themesPath  = $core->getSetting('themes_path');
        $this->filesPublic = $core->getPath('files_public');
        $this->basePath    = $core->getRequest()->getBasePath();
        $this->pathViews   = dirname(__DIR__) . '/Views/';

        $this->loadAssets();
    }

    public function __toString()
    {
        $scriptsInline = $this->getBlock('this')->getVar('scripts_inline');
        $this->view('this', [
            'meta'          => $this->makeBalise('meta', $this->meta),
            'script_inline' => $this->makeConfigJs() . $scriptsInline,
            'styles'        => $this->makeBalise('link', (self::$stylesGlobal + $this->styles)),
            'scripts'       => $this->makeBalise('script', (self::$scriptsGlobal + $this->scripts), false)
        ]);

        $content    = $this->getThemplate()->render();
        $this->body = new Stream($content);

        return parent::__toString();
    }

    public function init()
    {
        $this->loadComposer();
        $messages = $this->createBlock('messages.php', $this->pathViews)
            ->addVars([
            'errors'   => [],
            'warnings' => [],
            'infos'    => [],
            'success'  => []
        ]);

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
            ->addBlock('messages', $messages)
            ->addBlock('submenu', $submenu)
            ->addBlock('main_menu')
            ->addBlock('second_menu');

        $this->template = $this->createBlock('html.php', $this->pathViews)
            ->addBlock('page', $page)
            ->addVars([
                'dark'        => $this->isDarkTheme ? 'dark trumbowyg-dark' : '',
                'title'       => '',
                'logo'        => '',
                'favicon'     => '',
                'description' => '',
                'keyboard'    => '',
                'scripts_inline' => ''
            ])
            ->addVars($this->core->getSettings());
    }

    public function getTheme($theme = self::THEME_PUBLIC)
    {
        $granted = $this->core->callHook('app.granted', [ 'template.admin' ]);

        if ($theme === self::THEME_ADMIN && $granted) {
            $this->defaultThemeName = self::THEME_ADMIN;
            $this->isDarkTheme      = $this->config[ 'settings.theme_admin_dark' ];
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

    public function isTheme($themeName)
    {
        return $this->defaultThemeName === $themeName;
    }

    /**
     * Ajoute des variables à la template courante ou à une sous template.
     *
     * @param string $parent
     * @param array  $vars
     *
     * @return $this
     */
    public function view($parent, array $vars)
    {
        $this->getBlock($parent)->addVars($vars);

        return $this;
    }

    /**
     * Ajoute un bloc à la template courante ou une sous template.
     * Ce bloc peut recevoir des variables fournit en dernier paramètre de la fonction.
     *
     * @param sring  $parent
     * @param string $tpl
     * @param string $tplPath
     * @param array  $vars
     *
     * @return $this
     */
    public function make($selector, $tpl, $tplPath, array $vars = [])
    {
        $template = $this->createBlock($tpl, $tplPath);

        return $this->addBlock($selector, $template, $vars);
    }

    public function addFilterVar($selector, $key, callable $function)
    {
        $this->getBlock($selector)->addFilterVar($key, $function);

        return $this;
    }

    public function addFilterBlock($selector, $key, callable $function)
    {
        $this->getBlock($selector)->addFilterBlock($key, $function);

        return $this;
    }

    public function addFilterOutput($selector, $key, callable $function)
    {
        $this->getBlock($selector)->addFilterOutput($key, $function);

        return $this;
    }

    public function override($selector, array $templates)
    {
        $this->getBlock($selector)->setNamesOverride($templates);

        return $this;
    }

    /*
     * @param string $selector
     *
     * @return \Soosyze\Components\Template\Template
     */
    public function getBlock($selector)
    {
        return $this->getThemplate()->getBlockWithParent($selector);
    }

    /**
     * @return Block
     */
    public function createBlock($tpl, $tplPath)
    {
        return (new Block($tpl, $tplPath))
                ->addVars([
                    'base_path'  => $this->basePath,
                    'base_theme' => $this->basePath . $this->defaultThemePath . '/'
                ])
                ->addPathOverride($this->getPathTheme());
    }

    public function addBlock($selector, Block $template, array $vars = [])
    {
        if ($template !== null) {
            $template->addVars($vars);
        }

        sscanf($selector, '%[a-z].%s', $parent, $child);

        if ($child) {
            $this->getBlock($parent)->addBlock($child, $template);
        } else {
            $this->getThemplate()->addBlock($parent, $template);
        }

        return $this;
    }

    public function getPathTheme()
    {
        return is_dir(ROOT . $this->defaultThemePath)
            ? ROOT . $this->defaultThemePath . '/'
            : $this->defaultThemePath;
    }

    public function getSections()
    {
        if (!$this->composer) {
            $this->loadComposer();
        }

        return !empty($this->composer[ 'extra' ][ 'soosyze' ][ 'sections' ])
            ? $this->composer[ 'extra' ][ 'soosyze' ][ 'sections' ]
            : [];
    }

    public function loadComposer()
    {
        $pathTheme = $this->getPathTheme();
        if (is_file($pathTheme . 'composer.json')) {
            $this->composer = Util::getJson($pathTheme . 'composer.json');
        }
    }

    public function addMetas(array $meta)
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    public function addMeta(array $meta)
    {
        $this->meta[] = $meta;

        return $this;
    }

    public function addScript($name, $href, array $attr = [])
    {
        $this->scripts[$name] = [ 'src' => $href, 'type' => 'text/javascript' ] + $attr;

        return $this;
    }

    public function addStyle($name, $src, array $attr= [])
    {
        $this->styles[ $name ] = [ 'href' => $src, 'rel' => 'stylesheet' ] + $attr;

        return $this;
    }

    public function addConfigJs($name, $value)
    {
        $this->configJs[ $name ] = $value;

        return $this;
    }

    public static function setStylesGlobal($styles)
    {
        self::$stylesGlobal = $styles;
    }

    public static function setScriptsGlobal($scritps)
    {
        self::$scriptsGlobal = $scritps;
    }

    private function loadAssets()
    {
        $vendor = $this->core->getPath('modules', 'modules/core', false) . '/Template/Assets';

        $this->scripts = [
            'core'         => [
                'src' => "$vendor/js/script.js"
            ]
        ];
    }

    private function makeBalise($type, array $data, $orphan = true)
    {
        $out = '';
        foreach ($data as $attrs) {
            $out .= $orphan
                ? sprintf('<%s%s/>', $type, $this->renderAttrInput($attrs)) . PHP_EOL
                : sprintf('<%s%s></%s>', $type, $this->renderAttrInput($attrs), $type) . PHP_EOL;
        }

        return $out;
    }

    private function makeConfigJs()
    {
        return sprintf('<script>var config =%s</script>', json_encode($this->configJs)) . PHP_EOL;
    }

    private function renderAttrInput(array $attr)
    {
        $html = '';
        foreach ($attr as $key => $value) {
            $html .= sprintf(' %s="%s"', htmlspecialchars($key), htmlentities($value));
        }

        return $html;
    }

    private function getThemplate()
    {
        if ($this->template) {
            return $this->template;
        }
        $this->getTheme();

        return $this->template;
    }
}
