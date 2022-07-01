<?php

return [
    'alias' => [
        'class' => 'Soosyze\Core\Modules\System\Services\Alias'
    ],
    'composer' => [
        'class' => 'Soosyze\Core\Modules\System\Services\Composer'
    ],
    'semver' => [
        'class' => 'Soosyze\Core\Modules\System\Services\Semver'
    ],
    'module' => [
        'class' => 'Soosyze\Core\Modules\System\Services\Modules'
    ],
    'migration' => [
        'class' => 'Soosyze\Core\Modules\System\Services\Migration'
    ],
    'system.hook.app' => [
        'class' => 'Soosyze\Core\Modules\System\Hook\App',
        'hooks' => [
            'app.response.before' => 'hookSys',
            'app.403' => 'hooks403',
            'app.404' => 'hooks404',
            'app.503' => 'hooks503',
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'system.extend' => [
        'class' => 'Soosyze\Core\Modules\System\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'system.hook.user' => [
        'class' => 'Soosyze\Core\Modules\System\Hook\User',
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
        'class' => 'Soosyze\Core\Modules\System\Hook\Config',
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'email.hook.config' => [
        'class' => 'Soosyze\Core\Modules\System\Hook\ConfigEmail',
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ]
];
