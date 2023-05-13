<?php

$vendor = ROOT . 'vendor/';

/**
 * L'autoload de Soosyze charge les objets à la volée, sans map ni cache.
 * Utilisation conseilliez pour les environnements de dev ou pour déboguer.
 */

require $vendor . 'composer/ClassLoader.php';
$loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));

$loader->add('Parsedown', $vendor . 'erusev/parsedown');
$loader->addPsr4('Soosyze\\Core\\Modules\\', 'core/modules');
$loader->addPsr4('Soosyze\\Core\\Themes\\', 'core/themes');
$loader->addPsr4('Soosyze\\App\\Modules\\', 'app/modules');
$loader->addPsr4('Soosyze\\App\\Themes\\', 'app/themes');
$loader->addPsr4('Soosyze\\Kses\\', $vendor . 'soosyze/kses/src');
$loader->addPsr4('Soosyze\\Queryflatfile\\', $vendor . 'soosyze/queryflatfile/src');
$loader->addPsr4('Soosyze\\', $vendor . 'soosyze/framework/src');
$loader->addPsr4('Psr\\Http\\Message\\', [
    $vendor . 'psr/http-message/src',
    $vendor . 'psr/http-factory/src'
]);
$loader->addPsr4('Psr\\Http\\Client;', $vendor . 'psr/http-client/src');
$loader->addPsr4('Psr\\Container\\', $vendor . 'psr/container/src');
$loader->addPsr4('Psr\\Container\\', $vendor . 'psr/container/src');
$loader->addPsr4('PHPMailer\\PHPMailer\\', $vendor . 'phpmailer/phpmailer/src');
$loader->addPsr4('Composer\\Semver\\', $vendor . 'composer/semver/src');

$loader->register();

/**
 * Vous pouvez utilisez l'autoload de composer pour de meilleurs performance
 * http://www.darwinbiler.com/how-does-the-replace-property-work-in-composer/
 */

/* require ROOT . 'vendor/autoload.php'; */
