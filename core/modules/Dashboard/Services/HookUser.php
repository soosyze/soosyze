<?php

namespace SoosyzeCore\Dashboard\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Menu' ] = [
            'dashboard.administer' => t('Use the dashboard')
        ];
    }

    public function hookDashboardAdminister()
    {
        return 'dashboard.administer';
    }
}
