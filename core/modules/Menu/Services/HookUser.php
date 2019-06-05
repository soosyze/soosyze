<?php

namespace SoosyzeCore\Menu\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Menu' ] = [
            'menu.administer' => 'Administrer les menus et les éléments de menus'
        ];
    }
    
    public function hookMenuAdminister()
    {
        return 'menu.administer';
    }
}
