<?php

use Soosyze\Core\Modules\System\Extend;
use Soosyze\Core\Modules\System\Hook;
use Soosyze\Core\Modules\System\Services;

return [
    'alias' => [
        'class' => Services\Alias::class
    ],
    'composer' => [
        'class' => Services\Composer::class
    ],
    'semver' => [
        'class' => Services\Semver::class
    ],
    'module' => [
        'class' => Services\Modules::class
    ],
    'migration' => [
        'class' => Services\Migration::class
    ],
    'system.hook.app' => [
        'class' => Hook\App::class,
        'hooks' => [
            'app.response.before' => 'hookSys',
            'app.403' => 'hooks403',
            'app.404' => 'hooks404',
            'app.503' => 'hooks503',
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'system.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'system.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.system.module.edit' => 'hookModuleManage',
            'route.system.module.update' => 'hookModuleManage',
            'route.system.migration.check' => 'hookModuleManage',
            'route.system.migration.update' => 'hookModuleManage',
            'route.system.theme.index' => 'hookThemeManage',
            'route.system.theme.admin' => 'hookThemeManage',
            'route.system.theme.active' => 'hookThemeManage',
            'route.system.theme.edit' => 'hookThemeManage',
            'route.system.theme.update' => 'hookThemeManage',
            'route.system.tool.admin' => 'hookToolManage',
            'route.system.tool.cron' => 'hookToolAction',
            'route.system.tool.trans' => 'hookToolAction',
            'route.system.api.route' => 'apiRoute'
        ]
    ],
    'system.hook.config' => [
        'class' => Hook\Config::class,
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'email.hook.config' => [
        'class' => Hook\ConfigEmail::class,
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ]
];
