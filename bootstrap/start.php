<?php

/* Démarrage de la session. */
session_start();

/* Définit par défaut la timezone. */
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

require_once $basePath . 'app/app_core.php';

$req = Soosyze\Components\Http\ServerRequest::create();

$app = \Core::getInstance($req);

$app->setSettings([
    'config'              => 'app/config',
    /* Chemin des fichiers */
    'files'               => 'app/files',
    /* Chemin des fichiers public */
    'files_public'        => 'app/files/public',
    /* Chemin des modules du core. */
    'modules'             => 'core/modules/',
    /* Chemin des modules contributeur. */
    'modules_contributed' => 'app/modules/',
    /* Chemins des themes par odre de prioritée d'appel. */
    'themes_path'         => ['app/themes', 'core/themes']
]);

$app->init();

echo $app->run();
