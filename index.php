<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__ . DS);

$config[ 'debug' ] = true;
require_once 'bootstrap/requirements.php';
require_once 'bootstrap/debug.php';
require_once 'bootstrap/autoload.php';

require_once 'app/app_core.php';
require_once 'bootstrap/start.php';
require_once 'bootstrap/facade.php';

$response = \Core::getInstance()->run();
if ($response instanceof \SoosyzeCore\Template\Services\Templating) {
    echo $response;
} else {
    $emitter = new \Soosyze\ResponseEmitter();
    echo $emitter->emit($response);
}
