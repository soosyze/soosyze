<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);
define('MODULES_CORE', ROOT . 'modules' . DS);
define('MODULES_CONTRIBUED', ROOT . 'app' . DS . 'modules' . DS);

session_start();
require_once 'bootstrap/autoload.php';
require_once 'bootstrap/start.php';
