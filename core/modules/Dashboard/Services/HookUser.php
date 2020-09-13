<?php

namespace SoosyzeCore\Dashboard\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Dashboard' ] = [
            'dashboard.administer' => t('Use the dashboard')
        ];
    }

    public function hookDashboardAdminister()
    {
        return 'dashboard.administer';
    }
}
