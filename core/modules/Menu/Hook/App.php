<?php

namespace SoosyzeCore\Menu\Hook;

use SoosyzeCore\Template\Services\Templating;

class App
{
    /**
     * @var \Soosyze\App
     */
    private $core;

    /**
     * @var Menu
     */
    private $menu;

    public function __construct($core, $menu)
    {
        $this->core = $core;
        $this->menu = $menu;
    }

    public function hookResponseAfter($request, &$response)
    {
        if (!($response instanceof Templating)) {
            return;
        }
        $nameMenu = $response->isTheme('theme')
            ? 'menu-main'
            : 'menu-admin';

        $response
            ->addBlock('page.main_menu', $this->menu->renderMenu($nameMenu))
            ->addBlock('page.second_menu', $this->menu->renderMenu('menu-user'));
    }

    public function hookMenuShowResponseAfter($request, &$response)
    {
        if (!($response instanceof Templating)) {
            return;
        }

        $vendor = $this->core->getPath('modules', 'modules/core', false);

        $response->addScript('menu', "$vendor/Menu/Assets/js/menu.js");
    }
}
