<?php

return [
    'block' => [
        'class' => 'SoosyzeCore\Block\Services\Block'
    ],
    'block.hook.app' => [
        'class' => 'SoosyzeCore\Block\Hook\App',
        'hooks' => [
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'block.hook.block' => [
        'class' => 'SoosyzeCore\Block\Hook\Block',
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.social' => 'hookSocial',
            'block.social.create.form' => 'hookSocialForm',
            'block.social.edit.form' => 'hookSocialForm',
            'block.map' => 'hookMap',
            'block.map.create.form' => 'hookMapForm',
            'block.map.store.validator' => 'hookMapValidator',
            'block.map.store.before' => 'hookMapBefore',
            'block.map.edit.form' => 'hookMapForm',
            'block.map.update.validator' => 'hookMapValidator',
            'block.map.update.before' => 'hookMapBefore',
            'block.video' => 'hookVideo',
            'block.video.create.form' => 'hookVideoForm',
            'block.video.store.validator' => 'hookVideoValidator',
            'block.video.store.before' => 'hookVideoBefore',
            'block.video.edit.form' => 'hookVideoForm',
            'block.video.update.validator' => 'hookVideoValidator',
            'block.video.update.before' => 'hookVideoBefore',
        ]
    ],
    'social.hook.config' => [
        'class' => 'SoosyzeCore\Block\Hook\Config',
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'block.hook.user' => [
        'class' => 'SoosyzeCore\Block\Hook\User',
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.block.section.admin' => 'hookBlockAdmin',
            'route.block.section.update' => 'hookBlockAdmin',
            'route.block.show' => 'hookBlockEdited',
            'route.block.create.form' => 'hookBlockCreated',
            'route.block.create.list' => 'hookBlockCreated',
            'route.block.create.show' => 'hookBlockEdited',
            'route.block.store' => 'hookBlockCreated',
            'route.block.edit' => 'hookBlockEdited',
            'route.block.update' => 'hookBlockEdited',
            'route.block.remove' => 'hookBlockDeleted',
            'route.block.delete' => 'hookBlockDeleted'
        ]
    ],
    'block.extend' => [
        'class' => 'SoosyzeCore\Block\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser'
        ]
    ]
];
