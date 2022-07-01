<?php

return [
    'contact.extend' => [
        'class' => 'Soosyze\Core\Modules\Contact\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'contact.hook.api.route' => [
        'class' => 'Soosyze\Core\Modules\Contact\Hook\ApiRoute',
        'hooks' => [
            'api.route' => 'apiRoute'
        ]
    ],
    'contact.hook.block' => [
        'class' => 'Soosyze\Core\Modules\Contact\Hook\Block',
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.contact' => 'hookContact'
        ]
    ],
    'contact.hook.user' => [
        'class' => 'Soosyze\Core\Modules\Contact\Hook\User',
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.contact.form' => 'hookContact',
            'route.contact.check' => 'hookContact'
        ]
    ]
];
