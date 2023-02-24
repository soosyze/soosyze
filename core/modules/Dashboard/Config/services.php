<?php

use Soosyze\Core\Modules\Dashboard\Extend;
use Soosyze\Core\Modules\Dashboard\Hook\User;
use Soosyze\Core\Modules\Dashboard\Services\Dashboard;

return [
    'dashboard' => [
        'class' => Dashboard::class
    ],
    'dashboard.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'dashboard.hook.user' => [
        'class' => User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.dashboard.index' => 'hookDashboardAdminister',
            'route.dashboard.info' => 'hookDashboardAdminister'
        ]
    ]
];
