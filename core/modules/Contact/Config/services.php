<?php

return [
    'contact.extend' => [
        'class' => 'SoosyzeCore\Contact\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser',
            'install.menu' => 'hookInstallMenu'
        ]
    ],
    'contact.hook.user' => [
        'class' => 'SoosyzeCore\Contact\Hook\User',
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.contact' => 'hookContact',
            'route.contact.check' => 'hookContact'
        ]
    ]
];
