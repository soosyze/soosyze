<?php

namespace SoosyzeCore\Menu\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Menu' ] = [
            'menu.administer' => t('Administer menus and menu items')
        ];
    }

    public function hookMenuAdminister()
    {
        return 'menu.administer';
    }
    
    public function hookMenuDelete($name)
    {
        return in_array($name, [ 'menu-main', 'menu-admin', 'menu-user' ])
            ? false
            : 'menu.administer';
    }
}
