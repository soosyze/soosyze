<?php

namespace System\Services;

use Soosyze\Components\Template\Template;
use Soosyze\Components\Util\Util;

// Templates System
define("TPL", 'html.php');
define("TPL_PATH", VIEWS_SYSTEM);

// Folder Templates CMS
define("DEFAULT_TPL_PATH", 'app' . DS . 'themes');
define("ADMIN_TPL_PATH", 'themes');

class Templating extends \Soosyze\Components\Http\Reponse
{
    protected $template;

    protected $themeAdmin = true;

    protected $query;

    protected $core;

    protected $router;

    public function __construct($core, $router, $query)
    {
        $this->core   = $core;
        $this->router = $router;
        $this->query  = $query;
    }

    public function __toString()
    {
        $content    = $this->template->render();
        $this->body = new \Soosyze\Components\Http\Stream($content);

        return parent::__toString();
    }

    public function init()
    {
        parent::__construct();
        $templateHTML   = $this->themeOveride(TPL, TPL_PATH);
        $this->template = new Template($templateHTML->getName(), $templateHTML->getPath());

        $this->template->addBlock('page', $this->themeOveride('page.php', TPL_PATH));
        $this->template->addVars([
            'title'    => '',
            'styles'   => '',
            'scripts'  => '',
            'basePath' => $this->router->getBasePath(),
        ])->addVars($this->core->getSettings());

        $this->template->getBlock('page')
            ->addVars([
                'title_main' => '',
                'basePath'   => $this->router->getBasePath(),
            ])
            ->addVars($this->core->getSettings())
            ->addBlock('content')
            ->addBlock('messages')
            ->addBlock('main_menu')
            ->addBlock('second_menu');
    }

    public function setTheme($isTheme = true)
    {
        $this->themeAdmin = $isTheme;
        $this->init();

        return $this;
    }

    public function isThemeAdmin()
    {
        return $this->themeAdmin;
    }

    public function add(array $vars)
    {
        $this->template->addVars($vars);

        return $this;
    }

    public function themeOveride($tpl, $tplPath)
    {
        $dir = $this->query->select('value')
            ->from('option')
            ->where('name', 'theme')
            ->fetch();

        $dir = DEFAULT_TPL_PATH . DS . $dir[ 'value' ];

        if ($this->themeAdmin) {
            $dir = ADMIN_TPL_PATH . DS . 'admin';
        }

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file == $tpl) {
                        return new Template($file, $dir . DS);
                    }
                }
                closedir($dh);
            }
        }

        return new Template($tpl, $tplPath);
    }

    public function render($parent, $tpl, $tplPath, array $vars = null)
    {
        $template = $this->themeOveride($tpl, $tplPath);
        if (!empty($vars)) {
            $template->addVars($vars);
        }

        if (strstr($parent, '.', true)) {
            $this->template->getBlock(strstr($parent, '.', true))
                ->addBlock(substr(strstr($parent, '.'), 1), $template);
        } else {
            $this->template->addBlock($parent, $template);
        }

        return $this;
    }

    public function view($key, array $vars)
    {
        $this->template->getBlock($key)->addVars($vars);

        return $this;
    }

    public function getThemes()
    {
        return Util::getFolder(DEFAULT_TPL_PATH);
    }

    public function getThemesAdmin()
    {
        return Util::getFolder(ADMIN_TPL_PATH);
    }
}
