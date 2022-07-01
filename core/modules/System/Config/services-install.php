<?php

return [
    'install' => [
        'class' => 'Soosyze\Core\Modules\System\Hook\AppInstall',
        'hooks' => [
            'app.404' => 'hook404'
        ]
    ],
    'install.hook.install' => [
        'class' => 'Soosyze\Core\Modules\System\Hook\Step',
        'hooks' => [
            'step' => 'hookStep',
            'step.language' => 'hookLanguage',
            'step.language.check' => 'hookLanguageCheck',
            'step.profil' => 'hookProfil',
            'step.profil.check' => 'hookProfilCheck',
            'step.user' => 'hookUser',
            'step.user.check' => 'hookUserCheck',
            'step.install.modules.blog' => 'hookModules',
            'step.install.modules.site' => 'hookModules',
            'step.install.finish.site' => 'hookSite',
            'step.install.finish.blog' => 'hookBlog',
            'step.install.finish.portfolio' => 'hookPortfolio',
            'step.install.finish.one_page' => 'hookOnePage'
        ]
    ]
];
