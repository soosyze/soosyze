<?php

return [
    'filemanager' => [
        'class' => 'SoosyzeCore\FileManager\Services\FileManager',
        'arguments' => ['@core', '@filemanager.hook.user', '@router']
    ],
    'fileprofil' => [
        'class' => 'SoosyzeCore\FileManager\Services\FileProfil',
        'arguments' => ['@query']
    ],
    'filemanager.filter.iterator' => [
        'class' => 'SoosyzeCore\FileManager\Services\FilterManagerIterator',
        'arguments' => ['@filemanager.hook.user']
    ],
    'filemanager.extend' => [
        'class' => 'SoosyzeCore\FileManager\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'filemanager.hook.app' => [
        'class' => 'SoosyzeCore\FileManager\Hook\App',
        'arguments' => ['@core'],
        'hooks' => [
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'filemanager.hook.config' => [
        'class' => 'SoosyzeCore\FileManager\Hook\Config',
        'arguments' => ['@core'],
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'filemanager.hook.menu' => [
        'class' => 'SoosyzeCore\FileManager\Hook\Menu',
        'arguments' => ['@router', '@user'],
        'hooks' => [
            'user.submenu' => 'hookUsersMenu',
            'user.manager.submenu' => 'hookUserManagerSubmenu'
        ]
    ],
    'filemanager.hook.user' => [
        'class' => 'SoosyzeCore\FileManager\Hook\User',
        'arguments' => ['@fileprofil', '@user'],
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.filemanager.profil.admin' => 'hookFileAdmin',
            'route.filemanager.profil.admin.check' => 'hookFileAdmin',
            'route.filemanager.profil.create' => 'hookFileAdmin',
            'route.filemanager.profil.store' => 'hookFileAdmin',
            'route.filemanager.profil.edit' => 'hookFileAdmin',
            'route.filemanager.profil.update' => 'hookFileAdmin',
            'route.filemanager.profil.remove' => 'hookFileAdmin',
            'route.filemanager.profil.delete' => 'hookFileAdmin',
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
            'route.filemanager.copy.show' => 'hookFileShow',
            'route.filemanager.folder.create' => 'hookFolderStore',
            'route.filemanager.folder.store' => 'hookFolderStore',
            'route.filemanager.folder.edit' => 'hookFolderUpdate',
            'route.filemanager.folder.update' => 'hookFolderUpdate',
            'route.filemanager.folder.remove' => 'hookFolderDelete',
            'route.filemanager.folder.delete' => 'hookFolderDelete'
        ]
    ],
    'filemanager.hook.role' => [
        'class' => 'SoosyzeCore\FileManager\Hook\Role',
        'arguments' => ['@query'],
        'hooks' => [
            'role.delete.before' => 'hookRoleDeleteBefore'
        ]
    ]
];
