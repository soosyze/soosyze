<?php

$vendor = ROOT . 'vendor/';

/**
 * L'autoload de Soosyze charge les objets à la volée, sans map ni cache.
 * Utilisation conseilliez pour les environnements de dev ou pour déboguer.
 */
require_once $vendor . 'soosyze/framework/src/Autoload.php';
require_once $vendor . 'erusev/parsedown/Parsedown.php';

$autoload = new Soosyze\Autoload([
    'Soosyze\Core\Modules'  => ROOT . 'core/modules',
    'Soosyze\Core\Themes'   => ROOT . 'core/themes',
    'Soosyze\App\Modules'   => ROOT . 'app/modules',
    'Soosyze\App\Themes'    => ROOT . 'app/themes',
    'Soosyze'               => $vendor . 'soosyze/framework/src',
    'Soosyze\Queryflatfile' => $vendor . 'soosyze/queryflatfile/src',
    'Composer\Semver'       => $vendor . 'composer/semver/src'
    ]);

$autoload->setPrefix([
    'Soosyze\Queryflatfile'   => $vendor . 'soosyze/queryflatfile/src',
    'Psr\Http\Message'        => $vendor . 'psr/http-message/src',
    'Psr\Container'           => $vendor . 'psr/container/src',
    'Soosyze'                 => $vendor . 'soosyze/framework/src',
    'Soosyze\Kses'            => $vendor . 'soosyze/kses/src',
    'Soosyze\Components\Http' => $vendor . 'soosyze/framework/src/Components/Http',
    'PHPMailer\PHPMailer'     => $vendor . 'phpmailer/phpmailer/src'
]);

$autoload->register();

/*
 * Vous pouvez utilisez l'autoload de composer pour de meilleurs performance
 * http://www.darwinbiler.com/how-does-the-replace-property-work-in-composer/
 */

/* require ROOT . 'vendor/autoload.php'; */
