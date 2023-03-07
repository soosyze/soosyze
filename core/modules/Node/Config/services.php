<?php

use Soosyze\Core\Modules\Node\Extend;
use Soosyze\Core\Modules\Node\Hook;
use Soosyze\Core\Modules\Node\Services;

return [
    'node' => [
        'class' => Services\Node::class
    ],
    'nodeuser' => [
        'class' => Services\NodeUser::class
    ],
    'node.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu',
            'uninstall.menu' => 'hookUninstallMenu'
        ]
    ],
    'node.hook.block' => [
        'class' => Hook\Block::class,
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.node.next_previous' => 'hookBlockNextPrevious',
            'block.node.next_previous.edit.form' => 'hookNodeNextPreviousEditForm',
            'block.node.next_previous.update.validator' => 'hookNodeNextPreviousUpdateValidator',
            'block.node.next_previous.update.before' => 'hookNodeNextPreviousUpdateBefore'
        ]
    ],
    'node.hook.config' => [
        'class' => Hook\Config::class,
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'node.hook.filemanager' => [
        'class' => Hook\FileManager::class,
        'hooks' => [
            'node.create.form' => 'hookNodeCreateForm',
            'node.edit.form' => 'hookNodeEditForm',
            'entity.create.form' => 'hookEntityForm',
            'entity.edit.form' => 'hookEntityForm'
        ]
    ],
    'node.hook.app' => [
        'class' => Hook\App::class,
        'hooks' => [
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'node.hook.api.route' => [
        'class' => Hook\ApiRoute::class,
        'hooks' => [
            'api.route' => 'apiRoute'
        ]
    ],
    'node.hook.url' => [
        'class' => Hook\Url::class,
        'hooks' => [
            'node.create.form.data' => 'hookCreateFormData',
            'node.create.form' => 'hookCreateForm',
            'node.store.validator' => 'hookStoreValidator',
            'node.store.after' => 'hookStoreAfter',
            'node.edit.form.data' => 'hookEditFormData',
            'node.edit.form' => 'hookCreateForm',
            'node.update.validator' => 'hookStoreValidator',
            'node.update.after' => 'hookUpdateValid',
            'node.delete.after' => 'hookDeleteValid'
        ]
    ],
    'node.hook.menu' => [
        'class' => Hook\Menu::class,
        'hooks' => [
            'node.fieldset.submenu' => 'hookNodeFieldsetSubmenu',
            'node.create.form.data' => 'hookCreateFormData',
            'node.create.form' => 'hookCreateForm',
            'node.store.validator' => 'hookStoreValidator',
            'node.store.after' => 'hookStoreValid',
            'node.edit.form.data' => 'hookEditFormData',
            'node.edit.form' => 'hookCreateForm',
            'node.update.validator' => 'hookStoreValidator',
            'node.update.after' => 'hookUpdateValid',
            'node.delete.after' => 'hookDeleteValid',
            'menu.link.delete.after' => 'hookLinkDeleteValid'
        ]
    ],
    'node.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.node.admin' => 'hookNodeManager',
            'route.node.filter' => 'hookNodeManager',
            'route.node.filter.page' => 'hookNodeManager',
            'route.node.add' => 'hookNodeAdd',
            'route.node.show' => 'hookNodeSow',
            'route.node.create' => 'hookNodeCreated',
            'route.node.store' => 'hookNodeCreated',
            'route.node.edit' => 'hookNodeEdited',
            'route.node.clone' => 'hookNodeClone',
            'route.node.update' => 'hookNodeEdited',
            'route.node.remove' => 'hookNodeDeleted',
            'route.node.delete' => 'hookNodeDeleted',
            'route.node.api.remove' => 'hookNodeDeleted',
            'route.node.api.delete' => 'hookNodeDeleted',
            'route.node.entity.create' => 'hookEntityCreated',
            'route.node.entity.store' => 'hookEntityCreated',
            'route.node.entity.edit' => 'hookEntityEdited',
            'route.node.entity.update' => 'hookEntityEdited',
            'route.node.entity.delete' => 'hookEntityDeleted',
            'route.node.status.search' => 'hookNodeManager',
            'route.node.type.search' => 'hookNodeManager'
        ]
    ],
    'node.hook.nodeuser' => [
        'class' => Hook\NodeUser::class,
        'hooks' => [
            'user.show' => 'hookUserShow',
            'user.delete.after' => 'hookUserDeleteAfter'
        ]
    ],
    'node.hook.cron' => [
        'class' => Hook\Cron::class,
        'arguments' => [
            'nodeCron' => '#settings.node_cron'
        ],
        'hooks' => [
            'app.cron' => 'hookCron'
        ]
    ]
];
