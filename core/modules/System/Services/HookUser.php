<?php

namespace SoosyzeCore\System\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'System' ] = [
            'system.module.manage'      => 'Administer the modules',
            'system.theme.manage'       => 'Administer the themes',
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
}
