<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

RouteCollection::setNamespace('Soosyze\Core\Modules\System\Controller')->name('system.')->prefix('/admin')->group(function (RouteGroup $r): void {
    $r->get('api.route', '/api/route', '\RouteApi@index');

    $r->prefix('/modules')->name('module.')->setNamespace('\ModulesManager')->group(function (RouteGroup $r): void {
        $r->get('edit', '/', '@edit');
        $r->post('update', '/', '@update');
    });
    $r->prefix('/migration')->name('migration.')->setNamespace('\ModulesMigration')->group(function (RouteGroup $r): void {
        $r->get('check', '/check', '@check');
        $r->get('update', '/update', '@update');
    });
    $r->prefix('/theme')->name('theme.')->setNamespace('\Theme')->group(function (RouteGroup $r): void {
        $r->get('index', '/', '@admin');
        $r->get('admin', '/{type}', '@admin', [ 'type' => 'admin|public' ]);
        $r->get('active', '/{type}/active/{name}', '@active', [
            'type' => 'admin|public', 'name' => '\w+'
        ]);
        $r->get('edit', '/{type}/edit', '@edit', [ 'type' => 'admin|public' ]);
        $r->post('update', '/{type}/edit', '@update', [ 'type' => 'admin|public' ]);
    });
    $r->prefix('/tool')->name('tool.')->setNamespace('\Tool')->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
        $r->get('cron', '/cron', '@cron');
        $r->get('trans', '/trans', '@updateTranslations');
    });
});
