<?php

return [
    'dashboard' => [
        'class' => 'Soosyze\Core\Modules\Dashboard\Services\Dashboard'
    ],
    'dashboard.extend' => [
        'class' => 'Soosyze\Core\Modules\Dashboard\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'dashboard.hook.user' => [
        'class' => 'Soosyze\Core\Modules\Dashboard\Hook\User',
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.dashboard.index' => 'hookDashboardAdminister',
            'route.dashboard.info' => 'hookDashboardAdminister'
        ]
    ]
];
