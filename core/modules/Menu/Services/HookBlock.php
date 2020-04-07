<?php

namespace SoosyzeCore\Menu\Services;

class HookBlock
{
    protected $menu;

    public function __construct($menu)
    {
        $this->menu = $menu;
    }

    public function hookCreateFormData(array &$blocks)
    {
        $menus = $this->menu->getAllMenu();

        foreach ($menus as $menu) {
            $blocks[ "menu.{$menu[ 'name' ]}" ] = [
                'title'   => t($menu[ 'title' ]),
                'tpl'     => "block_menu-{$menu[ 'name' ]}.php",
                'path'    => $this->menu->getPathViews(),
                'hook'    => 'menu',
                'options' => [ 'name' => $menu[ 'name' ] ]
            ];
        }
    }

    public function hookBlockMenu($tpl, array $options)
    {
        if ($menu = $this->menu->renderMenu($options[ 'name' ])) {
            return $menu->setName('block_menu.php')
                    ->addNamesOverride([ 'block_menu_' . $options[ 'name' ] ]);
        }
    }
}
