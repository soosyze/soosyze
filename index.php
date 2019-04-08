<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__ . DS);
define('MODULES_CORE', ROOT . 'core' . DS . 'modules' . DS);
define('MODULES_CONTRIBUED', ROOT . 'app' . DS . 'modules' . DS);

//$config[ 'debug' ] = true;
require_once 'bootstrap/requirements.php';
require_once 'bootstrap/debug.php';
require_once 'bootstrap/autoload.php';

require_once 'app/app_core.php';
require_once 'bootstrap/start.php';

echo $app->run();
