<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

RouteCollection::setNamespace('SoosyzeCore\Config\Controller\Config')->name('config.')->prefix('/admin/config')->group(function (RouteGroup $r): void {
    $r->get('admin', '/', '@admin');
    $r->get('edit', '/:id', '@edit')->whereWords(':id');
    $r->put('update', '/:id', '@update')->whereWords(':id');
});
