<?php

return [
    'contact.extend' => [
        'class' => 'SoosyzeCore\Contact\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'contact.hook.api.route' => [
        'class' => 'SoosyzeCore\Contact\Hook\ApiRoute',
        'hooks' => [
            'api.route' => 'apiRoute'
        ]
    ],
    'contact.hook.block' => [
        'class' => 'SoosyzeCore\Contact\Hook\Block',
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.contact' => 'hookContact'
        ]
    ],
    'contact.hook.user' => [
        'class' => 'SoosyzeCore\Contact\Hook\User',
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.contact.form' => 'hookContact',
            'route.contact.check' => 'hookContact'
        ]
    ]
];
