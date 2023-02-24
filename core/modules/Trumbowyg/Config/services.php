<?php

use Soosyze\Core\Modules\Trumbowyg\Extend;
use Soosyze\Core\Modules\Trumbowyg\Hook;

return [
    'trumbowyg.install' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser'
        ]
    ],
    'trumbowyg.hook.app' => [
        'class' => Hook\App::class,
        'hooks' => [
            'node.create.response.after' => 'getEditor',
            'node.edit.response.after' => 'getEditor',
            'block.section.admin.response.after' => 'getEditor'
        ]
    ],
    'trumbowyg.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.trumbowyg.upload' => 'hookUpload'
        ]
    ]
];
