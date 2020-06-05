<?php

namespace SoosyzeCore\Dashboard\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Menu' ] = [
            'dashboard.administer' => t('Utiliser le dashboard')
        ];
    }

    public function hookDashboardAdminister()
    {
        return 'dashboard.administer';
    }
}
