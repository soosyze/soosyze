<?php

use Soosyze\Core\Modules\FileManager\Extend;
use Soosyze\Core\Modules\FileManager\Hook;
use Soosyze\Core\Modules\FileManager\Services;

return [
    'filemanager' => [
        'class' => Services\FileManager::class
    ],
    'fileprofil' => [
        'class' => Services\FileProfil::class
    ],
    'filemanager.filter.iterator' => [
        'class' => Services\FilterManagerIterator::class
    ],
    'filemanager.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'filemanager.hook.api.route' => [
        'class' => Hook\ApiRoute::class,
        'hooks' => [
            'api.route' => 'apiRoute'
        ]
    ],
    'filemanager.hook.app' => [
        'class' => Hook\App::class,
        'hooks' => [
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'filemanager.hook.config' => [
        'class' => Hook\Config::class,
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'filemanager.hook.menu' => [
        'class' => Hook\Menu::class,
        'hooks' => [
            'user.submenu' => 'hookUsersMenu',
            'user.manager.submenu' => 'hookUserManagerSubmenu'
        ]
    ],
    'filemanager.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.filemanager.permission.admin' => 'hookFileAdmin',
            'route.filemanager.permission.admin.check' => 'hookFileAdmin',
            'route.filemanager.permission.create' => 'hookFileAdmin',
            'route.filemanager.permission.store' => 'hookFileAdmin',
            'route.filemanager.permission.edit' => 'hookFileAdmin',
            'route.filemanager.permission.update' => 'hookFileAdmin',
            'route.filemanager.permission.remove' => 'hookFileAdmin',
            'route.filemanager.permission.delete' => 'hookFileAdmin',
            'route.filemanager.admin' => 'hookFolderAdmin',
            'route.filemanager.public' => 'hookFolderShow',
            'route.filemanager.show' => 'hookFolderShow',
            'route.filemanager.filter' => 'hookFolderShow',
            'route.filemanager.file.show' => 'hookFileShow',
            'route.filemanager.file.create' => 'hookFileStore',
            'route.filemanager.file.store' => 'hookFileStore',
            'route.filemanager.file.edit' => 'hookFileUpdate',
            'route.filemanager.file.update' => 'hookFileUpdate',
            'route.filemanager.file.remove' => 'hookFileDelete',
            'route.filemanager.file.delete' => 'hookFileDelete',
            'route.filemanager.file.download' => 'hookFileDownlod',
            'route.filemanager.copy.admin' => 'hookFileAdmin',
            'route.filemanager.copy.update' => 'hookFileCopy',
            'route.filemanager.copy.show' => 'hookFolderShow',
            'route.filemanager.folder.create' => 'hookFolderStore',
            'route.filemanager.folder.store' => 'hookFolderStore',
            'route.filemanager.folder.edit' => 'hookFolderUpdate',
            'route.filemanager.folder.update' => 'hookFolderUpdate',
            'route.filemanager.folder.remove' => 'hookFolderDelete',
            'route.filemanager.folder.delete' => 'hookFolderDelete',
            'route.filemanager.folder.download' => 'hookFolderDownload'
        ]
    ],
    'filemanager.hook.role' => [
        'class' => Hook\Role::class,
        'hooks' => [
            'role.delete.before' => 'hookRoleDeleteBefore'
        ]
    ]
];
