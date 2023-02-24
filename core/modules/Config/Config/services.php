<?php

use Soosyze\Core\Modules\Config\Extend;
use Soosyze\Core\Modules\Config\Hook\User;

return [
    'config.hook.user' => [
        'class' => User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.config.admin' => 'hookConfigAdmin',
            'route.config.edit' => 'hookConfigManage',
            'route.config.update' => 'hookConfigManage'
        ]
    ],
    'config.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ]
];
