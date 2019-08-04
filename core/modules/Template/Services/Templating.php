<?php

namespace SoosyzeCore\Template\Services;

use Soosyze\Components\Util\Util;

class Templating extends \Soosyze\Components\Http\Response
{
    /**
     * @var Block
     */
    protected $template;

    /**
     * @var \Soosyze\Config
     */
    protected $config;

    /**
     * @var \Soosyze\App
     */
    protected $core;

    /**
     * Nom du theme utilisé par défaut.
     *
     * @var string
     */
    protected $default_theme_name = '';

    /**
     * Chemin du thème.
     *
     * @var string
     */
    protected $default_theme_path = '';

    /**
     * Liste des répertoires contenant les thèmes.
     *
     * @var string[]
     */
    protected $themes_path = [];

    /**
     * Les données du fichier composer.json
     *
     * @var array
     */
    protected $composer = [];

    public function __construct($core, $config)
    {
        $this->core        = $core;
        $this->config      = $config;
        $this->themes_path = $core->getSetting('themes_path');
        $this->base_path   = $core->getRequest()->getBasePath();
        $this->pathViews   = dirname(__DIR__) . '/Views/';
        $this->getTheme();
    }

    public function __toString()
    {
        $content    = $this->template->render();
        $this->body = new \Soosyze\Components\Http\Stream($content);

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

        $page = $this->createBlock('page.php', $this->pathViews)
            ->addVars([
                'title'      => '',
                'title_main' => '',
                'logo'       => ''
            ])
            ->addVars($this->core->getSettings())
            ->addBlock('content')
            ->addBlock('messages', $messages)
            ->addBlock('main_menu')
            ->addBlock('second_menu');

        if (!empty($this->composer[ 'extra' ][ 'soosyze-theme' ][ 'blocks' ])) {
            foreach ($this->composer[ 'extra' ][ 'soosyze-theme' ][ 'blocks' ] as $newBlock) {
                $page->addBlock($newBlock);
            }
        }

        $this->template = $this->createBlock('html.php', $this->pathViews)
            ->addBlock('page', $page)
            ->addVars([
                'title'       => '',
                'logo'        => '',
                'favicon'     => '',
                'description' => '',
                'keyboard'    => '',
                'styles'      => '',
                'scripts'     => ''
            ])
            ->addVars($this->core->getSettings());
    }

    public function getTheme($theme = 'theme')
    {
        $this->default_theme_name = $theme;
        foreach ($this->themes_path as $path) {
            $dir = $path . '/' . $this->config->get('settings.' . $theme, '');
            if (is_dir($dir)) {
                $this->default_theme_path = $dir;

                break;
            }
        }
        $this->init();

        return $this;
    }

    public function isTheme($themeName)
    {
        return $this->default_theme_name === $themeName;
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
    public function render($parent, $tpl, $tplPath, array $vars = [])
    {
        $template = $this->createBlock($tpl, $tplPath)
            ->addVars($vars);

        if ($block = strstr($parent, '.', true)) {
            $this->getBlock($block)->addBlock(substr(strstr($parent, '.'), 1), $template);
        } else {
            $this->template->addBlock($parent, $template);
        }

        return $this;
    }

    public function addFilterVar($parent, $key, callable $function)
    {
        $this->getBlock($parent)->addFilterVar($key, $function);

        return $this;
    }

    public function addFilterBlock($parent, $key, callable $function)
    {
        $this->getBlock($parent)->addFilterBlock($key, $function);

        return $this;
    }

    public function addFilterOutput($parent, $key, callable $function)
    {
        $this->getBlock($parent)->addFilterOutput($key, $function);

        return $this;
    }

    public function override($parent, array $templates)
    {
        $this->getBlock($parent)->addNamesOverride($templates);

        return $this;
    }

    public function getBlock($parent)
    {
        return $this->template->getBlockWithParent($parent);
    }

    public function getThemes()
    {
        $folders = [];
        foreach ($this->themes_path as $path) {
            if (is_dir($path)) {
                $folders = array_merge($folders, Util::getFolder($path));
            }
        }

        return $folders;
    }

    public function createBlock($tpl, $tplPath)
    {
        return (new Block($tpl, $tplPath))
                ->addVars([
                    'base_path'  => $this->base_path,
                    'base_theme' => $this->base_path . $this->default_theme_path . '/'
                ])
                ->pathOverride($this->getPathTheme());
    }

    public function addBlock($parent, $template, array $vars = [])
    {
        if( $template !== null )
        {
            $template->addVars($vars);
        }

        if ($block = strstr($parent, '.', true)) {
            $this->getBlock($block)
                ->addBlock(substr(strstr($parent, '.'), 1), $template);
        } else {
            $this->template->addBlock($parent, $template);
        }

        return $this;
    }

    public function getPathTheme()
    {
        return is_dir(ROOT . $this->default_theme_path)
            ? ROOT . $this->default_theme_path . '/'
            : $this->default_theme_path;
    }

    public function loadComposer()
    {
        $pathTheme = $this->getPathTheme();
        if (is_file($pathTheme . 'composer.json')) {
            $this->composer = Util::getJson($pathTheme . 'composer.json');
        }
    }
}
