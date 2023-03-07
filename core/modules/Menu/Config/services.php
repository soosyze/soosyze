<?php

use Soosyze\Core\Modules\Menu\Extend;
use Soosyze\Core\Modules\Menu\Hook;
use Soosyze\Core\Modules\Menu\Services;

return [
    'menu' => [
        'class' => Services\Menu::class
    ],
    'menu.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.block' => 'hookInstallBlock',
            'install.user' => 'hookInstallUser'
        ]
    ],
    'menu.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.menu.admin' => 'hookMenuAdminister',
            'route.menu.create' => 'hookMenuAdminister',
            'route.menu.store' => 'hookMenuAdminister',
            'route.menu.show' => 'hookMenuAdminister',
            'route.menu.check' => 'hookMenuAdminister',
            'route.menu.edit' => 'hookMenuAdminister',
            'route.menu.update' => 'hookMenuAdminister',
            'route.menu.remove' => 'hookMenuDelete',
            'route.menu.delete' => 'hookMenuDelete',
            'route.menu.api.show' => 'hookMenuApiShow',
            'route.menu.link.create' => 'hookMenuAdminister',
            'route.menu.link.store' => 'hookMenuAdminister',
            'route.menu.link.edit' => 'hookMenuAdminister',
            'route.menu.link.update' => 'hookMenuAdminister',
            'route.menu.link.remove' => 'hookMenuAdminister',
            'route.menu.link.remove.modal' => 'hookMenuAdminister',
            'route.menu.link.delete' => 'hookMenuAdminister'
        ]
    ],
    'menu.hook.app' => [
        'class' => Hook\App::class,
        'hooks' => [
            'menu.admin.response.after' => 'hookMenuShowResponseAfter',
            'menu.show.response.after' => 'hookMenuShowResponseAfter'
        ]
    ],
    'menu.hook.block' => [
        'class' => Hook\Block::class,
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.menu' => 'hookMenu',
            'block.menu.create.form' => 'hookMenuForm',
            'block.menu.store.validator' => 'hookMenuValidator',
            'block.menu.store.before' => 'hookMenuBefore',
            'block.menu.edit.form' => 'hookMenuForm',
            'block.menu.update.validator' => 'hookMenuValidator',
            'block.menu.update.before' => 'hookMenuBefore',
            'menu.remove.form' => 'hookMenuRemoveForm',
            'menu.delete.before' => 'hookMenuDeleteBefore'
        ]
    ]
];
