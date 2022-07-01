<?php

return [
    'trumbowyg.install' => [
        'class' => 'Soosyze\Core\Modules\Trumbowyg\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser'
        ]
    ],
    'trumbowyg.hook.app' => [
        'class' => 'Soosyze\Core\Modules\Trumbowyg\Hook\App',
        'hooks' => [
            'node.create.response.after' => 'getEditor',
            'node.edit.response.after' => 'getEditor',
            'block.section.admin.response.after' => 'getEditor'
        ]
    ],
    'trumbowyg.hook.user' => [
        'class' => 'Soosyze\Core\Modules\Trumbowyg\Hook\User',
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.trumbowyg.upload' => 'hookUpload'
        ]
    ]
];
