<?php

/* Démarrage de la session. */
session_start();

/* Définit par défaut la timezone. polyfills */
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

$req = Soosyze\Components\Http\ServerRequest::create();

$app = \Core::getInstance($req);

$app->setSettings([
    'root'                => ROOT,
    'config'              => 'app/config',
    /* Chemin des fichiers public. */
    'files_public'        => 'app/files',
    /* Chemin des modules du core. */
    'modules'             => 'core/modules',
    /* Chemin des modules contributeur. */
    'modules_contributed' => 'app/modules',
    /* Chemins des thèmes par ordre de priorité d'appel. */
    'themes_path'         => [ 'app/themes', 'core/themes' ],
    /* Chemin des backups, absolu */
    'backup_dir'          => ROOT . '../soosyze_backups'
]);

$app->setEnvironmentDefault('default');

/* Définition des environnements par domaine ou nom de machine. */
/* $app->setEnvironnement([
        'local' => [ '127.0.0.1' ],
        'prod'  => [ 'https://foo.com' ]
    ]);
*/
$app->init();
