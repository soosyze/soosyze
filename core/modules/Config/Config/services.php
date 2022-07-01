<?php

return [
    'config.hook.user' => [
        'class' => 'Soosyze\Core\Modules\Config\Hook\User',
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.config.admin' => 'hookConfigAdmin',
            'route.config.edit' => 'hookConfigManage',
            'route.config.update' => 'hookConfigManage'
        ]
    ],
    'config.extend' => [
        'class' => 'Soosyze\Core\Modules\Config\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ]
];
