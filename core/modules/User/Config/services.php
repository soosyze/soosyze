<?php

use Soosyze\Core\Modules\User\Extend;
use Soosyze\Core\Modules\User\Hook;
use Soosyze\Core\Modules\User\Services;

return [
    'user' => [
        'class' => Services\User::class,
        'hooks' => [
            'app.granted' => 'isGranted',
            'app.granted.request' => 'isGrantedRequest',
            'app.response.before' => 'hookResponseBefore',
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'auth' => [
        'class' => Services\Auth::class
    ],
    'user.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'user.hook.api.route' => [
        'class' => Hook\ApiRoute::class,
        'arguments' => [
            'connectUrl' => '#settings.connect_url'
        ],
        'hooks' => [
            'api.route' => 'apiRoute'
        ]
    ],
    'user.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.user.login' => 'hookLogin',
            'route.user.login.check' => 'hookLoginCheck',
            'route.user.relogin' => 'hookRelogin',
            'route.user.relogin.check' => 'hookRelogin',
            'router.user.reset' => '',
            'route.user.logout' => 'hookLogout',
            'route.user.permission.admin' => 'hookPermissionAdminister',
            'route.user.permission.update' => 'hookPermissionAdminister',
            'route.user.register.create' => 'hookRegister',
            'route.user.register.store' => 'hookRegister',
            'route.user.activate' => 'hookActivate',
            'route.user.role.admin' => 'hookPeopleAdminister',
            'route.user.role.admin.check' => 'hookPeopleAdminister',
            'route.user.role.create' => 'hookPeopleAdminister',
            'route.user.role.store' => 'hookPeopleAdminister',
            'route.user.role.edit' => 'hookPeopleAdminister',
            'route.user.role.update' => 'hookPeopleAdminister',
            'route.user.role.remove' => 'hookRoleDeleted',
            'route.user.role.delete' => 'hookRoleDeleted',
            'route.user.account' => 'hookLogout',
            'route.user.show' => 'hookUserShow',
            'route.user.create' => 'hookPeopleAdminister',
            'route.user.store' => 'hookPeopleAdminister',
            'route.user.edit' => 'hookUserEdited',
            'route.user.update' => 'hookUserEdited',
            'route.user.remove' => 'hookUserDeleted',
            'route.user.delete' => 'hookUserDeleted',
            'route.user.admin' => 'hookPeopleAdminister',
            'route.user.filter' => 'hookPeopleAdminister',
            'route.user.filter.page' => 'hookPeopleAdminister',
            'route.user.api.select' => 'hookUserApiSelect'
        ]
    ],
    'user.hook.config' => [
        'class' => Hook\Config::class,
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'user.hook.block' => [
        'class' => Hook\Block::class,
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.user.login' => 'hookUserLogin',
        ]
    ]
];
