<?php

ini_set('magic_quotes_runtime', '0');

/*
 * @see https://www.php.net/manual/en/session.security.php
 */
ini_set('session.use_cookies', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.cache_limiter', '');
ini_set('session.cookie_httponly', '1');

/* Démarrage de la session. */
if (session_id() === '') {
    session_start();
}

/* Définit par défaut la timezone. polyfills */
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

$req = Soosyze\Components\Http\ServerRequest::create();

$app = \Core::getInstance($req);

$app->setSettings([
    'root'                => ROOT,
    /* Chemin des fichiers de configurations. */
    'config'              => 'app/config',
    /* Chemin des fichiers public. */
    'files_public'        => 'public/files',
    /* Chemin des ressources public. */
    'assets_public'       => 'public/vendor',
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
