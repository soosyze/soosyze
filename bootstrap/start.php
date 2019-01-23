<?php

/* Démarrage de la session. */
session_start();

require_once $basePath . 'app/app_core.php';

$app = \Core::getInstance();

$app->setSettings([
    'config'              => 'app/config',
    /* Chemin de la racine. */
    'base_path'           => '',
    /* Chemin des fichiers */
    'files'               => 'app/files',
    /* Chemin des modules du core. */
    'modules'             => 'modules/',
    /* Chemin des modules contributeur. */
    'modules_contributed' => 'app/modules/',
    /* Chemin des themes du core. */
    'themes'              => 'themes/',
    /* Chemin des theme contributeur. */
    'themes_contributed'  => 'app/themes/'
]);

$app->init();

echo $app->run();
