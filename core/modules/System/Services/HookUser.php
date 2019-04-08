<?php

namespace System\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'System' ] = [
            'system.config.manage'      => 'Administrer les configurations',
            'system.module.manage'      => 'Administrer les modules',
            'system.config.maintenance' => 'Accéder au site en mode maintenance'
        ];
    }

    public function hookConfigManage()
    {
        return 'system.config.manage';
    }

    public function hookModuleManage()
    {
        return 'system.module.manage';
    }
}
