<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\Dashboard\Controller\Dashboard;

RouteCollection::setNamespace(Dashboard::class)->name('dashboard.')->prefix('/admin/dashboard')->group(function (RouteGroup $r): void {
    $r->get('index', '/', '@index');
    $r->get('info', '/info', '@info');
});
