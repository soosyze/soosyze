<?php

namespace SoosyzeCore\System\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'System' ] = [
            'system.module.manage'      => t('Administer the modules'),
            'system.config.maintenance' => t('Access the site in maintenance mode')
        ];
    }

    public function hookModuleManage()
    {
        return 'system.module.manage';
    }
}
