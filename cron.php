<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__ . DS);

require_once ROOT . 'bootstrap/autoload.php';
require_once ROOT . 'app/app_core.php';
require_once ROOT . 'bootstrap/start_cli.php';

$get         = filter_input(INPUT_GET, 'q');
$key_cron    = $app->get('config')->get('settings.key_cron');
$maintenance = $app->get('config')->get('settings.maintenance');

/* N'exécute pas les scripts si le site est en maintenance. */
if ($maintenance) {
    exit();
}
/* Si le script est exécuté en cli. */
if (!(PHP_SAPI != 'cli' && PHP_SAPI != 'cgi' && PHP_SAPI != 'cgi-fcgi')) {
    $app->callHook('app.cron', [ $req ]);
}
/* Si le script est exécuté avec la clé du cron. */
elseif (!empty($get) && !empty($key_cron) && $get == $key_cron) {
    $app->callHook('app.cron', [ $req ]);
}
