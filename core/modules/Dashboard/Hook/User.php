<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Dashboard\Hook;

class User implements \Soosyze\Core\Modules\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
        $permissions[ 'Dashboard' ] = [
            'dashboard.administer' => 'Use the dashboard'
        ];
    }

    public function hookDashboardAdminister(): string
    {
        return 'dashboard.administer';
    }
}
