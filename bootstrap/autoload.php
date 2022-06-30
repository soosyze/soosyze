<?php

$vendor = ROOT . 'vendor/';

/**
 * L'autoload de Soosyze charge les objets à la volée, sans map ni cache.
 * Utilisation conseilliez pour les environnements de dev ou pour déboguer.
 */
require_once $vendor . 'soosyze/framework/src/Autoload.php';
require_once $vendor . 'erusev/parsedown/Parsedown.php';

$autoload = new Soosyze\Autoload([
    'Soosyze\Core\Themes' => ROOT . 'core/themes',
    'Soosyze\App\Themes'  => ROOT . 'app/themes',
    'Soosyze'             => $vendor . 'soosyze/framework/src',
    'Queryflatfile'       => $vendor . 'soosyze/queryflatfile/src',
    'SoosyzeCore'         => ROOT . 'core/modules',
    'SoosyzeExtension'    => ROOT . 'app/modules',
    'Composer\Semver'     => $vendor . 'composer/semver/src'
]);

$autoload->setPrefix([
    'Queryflatfile'           => $vendor . 'soosyze/queryflatfile/src',
    'Psr\Http\Message'        => $vendor . 'psr/http-message/src',
    'Psr\Container'           => $vendor . 'psr/container/src',
    'Soosyze'                 => $vendor . 'soosyze/framework/src',
    'Kses'                    => $vendor . 'soosyze/kses/src',
    'Soosyze\Components\Http' => $vendor . 'soosyze/framework/src/Components/Http',
    'PHPMailer\PHPMailer'     => $vendor . 'phpmailer/phpmailer/src'
])->setMap([
    ROOT . 'core/modules',
    ROOT . 'app/modules'
]);

$autoload->register();

/*
 * Vous pouvez utilisez l'autoload de composer pour de meilleurs performance
 * http://www.darwinbiler.com/how-does-the-replace-property-work-in-composer/
 */

/* require ROOT . 'vendor/autoload.php'; */
