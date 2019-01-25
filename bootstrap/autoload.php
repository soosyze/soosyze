<?php

$basePath = __DIR__ . '/../';
$vendor   = $basePath . 'vendor/';

/**
 * L'autoload de Soosyze charge les objets à la volée, sans map ni cache.
 * Utilisation conseilliez pour les environnements de dev ou pour déboguer.
 */
require_once $vendor . 'soosyze/framework/src/Autoload.php';
require_once $vendor . 'ircmaxell/password-compat/lib/password.php';

$autoload = new Soosyze\Autoload([
    'Soosyze'       => $vendor . 'soosyze/framework/src',
    'Queryflatfile' => $vendor . 'soosyze/queryflatfile/src'
    ]);

$autoload->setPrefix([
    'Queryflatfile'           => $vendor . 'soosyze/queryflatfile/src',
    'Psr\Http\Message'        => $vendor . 'psr/http-message/src',
    'Psr\Container'           => $vendor . 'psr/container/src',
    'Soosyze'                 => $vendor . 'soosyze/framework/src',
    'Soosyze\Components\Http' => $vendor . 'soosyze/framework/src/Components/Http'
])->setMap([
    $basePath . 'core/modules',
    $basePath . 'app/modules'
]);

$autoload->register();

/*
 * Vous pouvez utilisez l'autoload de composer pour de meilleurs performance
 * http://www.darwinbiler.com/how-does-the-replace-property-work-in-composer/
 */

//require $basePath . 'vendor/autoload.php';
