<?php

use Soosyze\Core\Modules\Contact\Extend;
use Soosyze\Core\Modules\Contact\Hook;

return [
    'contact.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'contact.hook.api.route' => [
        'class' => Hook\ApiRoute::class,
        'hooks' => [
            'api.route' => 'apiRoute'
        ]
    ],
    'contact.hook.block' => [
        'class' => Hook\Block::class,
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.contact' => 'hookContact'
        ]
    ],
    'contact.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.contact.form' => 'hookContact',
            'route.contact.check' => 'hookContact'
        ]
    ]
];
