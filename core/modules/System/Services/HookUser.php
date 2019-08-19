<?php

namespace SoosyzeCore\System\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'System' ] = [
            'system.module.manage'      => 'Administrer les modules',
            'system.config.maintenance' => 'Accéder au site en mode maintenance'
        ];
    }

    public function hookModuleManage()
    {
        return 'system.module.manage';
    }
}
