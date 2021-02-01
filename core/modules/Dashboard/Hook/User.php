<?php

namespace SoosyzeCore\Dashboard\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'Dashboard' ] = [
            'dashboard.administer' => 'Use the dashboard'
        ];
    }

    public function hookDashboardAdminister()
    {
        return 'dashboard.administer';
    }
}
