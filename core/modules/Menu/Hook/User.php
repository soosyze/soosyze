<?php

namespace SoosyzeCore\Menu\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'Menu' ] = [
            'menu.administer' => 'Administer menus and menu items'
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

    public function hookMenuApiShow($menu, $req, $user)
    {
        return !empty($user);
    }
}
