<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

RouteCollection::setNamespace('SoosyzeCore\System\Controller\Install')->name('install.')->prefix('/install')->group(function (RouteGroup $r): void {
    $r->get('index', '/', '@index');
    $r->get('step', '/step/{id}', '@step')->whereWords('id');
    $r->post('step.check', '/step/{id}', '@stepCheck')->whereWords('id');
    $r->post('language', '/language/{id}', 'Install@language')->whereWords('id');
});
