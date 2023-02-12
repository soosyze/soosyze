<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\System\Controller as Ctr;

RouteCollection::name('system.')->prefix('/admin')->group(function (RouteGroup $r): void {
    $r->get('api.route', '/api/route', Ctr\RouteApi::class . '@index');

    $r->prefix('/modules')->name('module.')->setNamespace(Ctr\ModulesManager::class)->group(function (RouteGroup $r): void {
        $r->get('edit', '/', '@edit');
        $r->post('update', '/', '@update');
    });
    $r->prefix('/migration')->name('migration.')->setNamespace(Ctr\ModulesMigration::class)->group(function (RouteGroup $r): void {
        $r->get('check', '/check', '@check');
        $r->get('update', '/update', '@update');
    });
    $r->prefix('/theme')->name('theme.')->setNamespace(Ctr\Theme::class)->group(function (RouteGroup $r): void {
        $r->get('index', '/', '@admin');
        $r->get('admin', '/{type}', '@admin', [ 'type' => 'admin|public' ]);
        $r->post('active', '/{type}/active', '@active', [
            'type' => 'admin|public'
        ]);
        $r->get('edit', '/{type}/edit', '@edit', [ 'type' => 'admin|public' ]);
        $r->post('update', '/{type}/edit', '@update', [ 'type' => 'admin|public' ]);
    });
    $r->prefix('/tool')->name('tool.')->setNamespace(Ctr\Tool::class)->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
        $r->get('cron', '/cron', '@cron');
        $r->get('trans', '/trans', '@updateTranslations');
    });
});
