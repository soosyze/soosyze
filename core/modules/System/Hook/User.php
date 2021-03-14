<?php

namespace SoosyzeCore\System\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'System' ] = [
            'system.module.manage'      => 'Administer the modules',
            'system.theme.manage'       => 'Administer the themes',
            'system.tool.manage'        => 'Use the tools interface',
            'system.tool.action'        => 'Use actions in the tools interface',
            'system.config.maintenance' => 'Access the site in maintenance mode'
        ];
    }

    public function hookModuleManage()
    {
        return 'system.module.manage';
    }

    public function hookThemeManage()
    {
        return 'system.theme.manage';
    }

    public function hookToolManage()
    {
        return 'system.tool.manage';
    }

    public function hookToolAction()
    {
        return 'system.tool.action';
    }

    public function apiRoute($req, $user)
    {
        return !empty($user);
    }
}
