<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\Config\Controller\Config;

RouteCollection::setNamespace(Config::class)->name('config.')->prefix('/admin/config')->group(function (RouteGroup $r): void {
    $r->get('admin', '/', '@admin');
    $r->get('edit', '/{id}', '@edit')->whereWords('id');
    $r->put('update', '/{id}', '@update')->whereWords('id');
});
