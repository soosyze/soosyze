<?php

use Soosyze\Components\Http\ServerRequest;
use Soosyze\Components\Http\Uri;

/* Démarrage de la session. */
session_start();

/* Définit par défaut la timezone. polyfills */
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

/* Home for Linux */
$home = isset($_SERVER[ 'HOME' ])
    ? rtrim($_SERVER[ 'HOME' ], '/')
    : '';
/* Home for Windows */
if (empty($home) && isset($_SERVER[ 'HOMEDRIVE' ], $_SERVER[ 'HOMEPATH' ])) {
    $home = rtrim(htmlspecialchars($_SERVER[ 'HOMEDRIVE' ] . $_SERVER[ 'HOMEPATH' ]), '\\/');
}
/* Construit une requête dédié à PHP CLI. */
$req = new ServerRequest(
    'GET',
    new Uri('http', $home, '/', 80, ''),
    [],
    null,
    '1.1',
    $_SERVER,
    [],
    []
);

$app = Core::getInstance($req);

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
    'backup_dir'          => '../soosyze_backups',
    /* Chemin du répertoire utilisé pour les fichiers temporaires. */
    'tmp_dir'             => sys_get_temp_dir()
]);

$app->setEnvironmentDefault('default');

/* Définition des environnements par domaine ou nom de machine. */
/* $app->setEnvironnement([
        'local' => [ '127.0.0.1' ],
        'prod'  => [ 'https://foo.com' ]
    ]);
*/
$app->init();
