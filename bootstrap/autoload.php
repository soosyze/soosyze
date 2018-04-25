<?php

$basePath = __DIR__ . '/../';
$vendor   = $basePath . 'vendor/';

/**
 * L'autoload de Soosyze charge les objets à la volée, sans map ni cache.
 * Utilisation conseilliez pour les environnements de dev ou pour déboguer.
 */
require $vendor . 'soosyze/framework/src/Autoload.php';

$autoload = new Soosyze\Autoload([
    'Soosyze'          => $vendor . 'soosyze/framework/src',
    'Queryflatfile'    => $vendor . 'soosyze/queryflatfile/src',
    'Psr\Http\Message' => $vendor . 'psr/http-message/src',
    'Psr\Container'    => $vendor . 'psr/container/src',
    ]);

$autoload->setMap([
    $basePath . 'modules',
    $basePath . 'app/modules',
]);

$autoload->register();

/**
 * Vous pouvez utilisez l'autoload de composer pour de meilleurs performance
 * http://www.darwinbiler.com/how-does-the-replace-property-work-in-composer/
 */

//require $basePath . 'vendor/autoload.php';
