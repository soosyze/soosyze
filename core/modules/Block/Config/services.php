<?php

use Soosyze\Core\Modules\Block\Extend;
use Soosyze\Core\Modules\Block\Hook;
use Soosyze\Core\Modules\Block\Services;

return [
    'block' => [
        'class' => Services\Block::class
    ],
    'style' => [
        'class' => 'Soosyze\Core\Modules\Block\Services\Style'
    ],
    'block.hook.app' => [
        'class' => Hook\App::class,
        'hooks' => [
            'app.response.after' => 'hookResponseAfter'
        ]
    ],
    'block.hook.block' => [
        'class' => Hook\Block::class,
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
        'class' => Hook\Config::class,
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'block.hook.user' => [
        'class' => Hook\User::class,
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
            'route.block.style.edit' => 'hookBlockEdited',
            'route.block.style.update' => 'hookBlockEdited',
            'route.block.remove' => 'hookBlockDeleted',
            'route.block.delete' => 'hookBlockDeleted',
            'route.block.tool.style' => 'hookBlockEdited'
        ]
    ],
    'block.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser'
        ]
    ],
    'block.hook.tool' => [
        'class' => 'Soosyze\Core\Modules\Block\Hook\Tool',
        'hooks' => [
            'tools.action' => 'hookToolAction'
        ]
    ]
];
