<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);
define('MODULES_CORE', ROOT . 'modules' . DS);
define('MODULES_CONTRIBUED', ROOT . 'app' . DS . 'modules' . DS);

session_start();
//$config[ 'debug' ] = true;
require_once 'bootstrap/debug.php';
require_once 'bootstrap/autoload.php';
require_once 'bootstrap/start.php';
