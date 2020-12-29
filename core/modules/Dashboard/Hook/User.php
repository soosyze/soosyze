<?php

namespace SoosyzeCore\Dashboard\Hook;

class User
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Dashboard' ] = [
            'dashboard.administer' => 'Use the dashboard'
        ];
    }

    public function hookDashboardAdminister()
    {
        return 'dashboard.administer';
    }
}
