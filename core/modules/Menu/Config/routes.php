<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

RouteCollection::setNamespace('SoosyzeCore\Menu\Controller')->name('menu.')->group(function (RouteGroup $r): void {
    $r->get('api.show', '/admin/api/menu/:menu', 'MenuApi@show', [ ':menu' => '[a-z\d-]+' ]);

    $r->prefix('/admin/menu')->group(function (RouteGroup $r): void {
        $r->setNamespace('\MenuManager')->group(function (RouteGroup $r): void {
            $r->get('admin', '/', '@admin');
            $r->get('show', '/:menu', '@show')->whereSlug(':menu');
            $r->patch('check', '/:menu', '@check')->whereSlug(':menu');
        });
        $r->setNamespace('\Menu')->group(function (RouteGroup $r) {
            $r->get('create', '/create', '@create');
            $r->post('store', '/create', '@store');
            $r->get('edit', '/:menu/edit', '@edit')->whereSlug(':menu');
            $r->put('update', '/:menu', '@update')->whereSlug(':menu');
            $r->get('remove', '/:menu/delete', '@remove')->whereSlug(':menu');
            $r->delete('delete', '/:menu', '@delete')->whereSlug(':menu');
        });

        $r->prefix('/:menu/link')->withs([ ':menu' => '[a-z\d-]+' ])->name('link.')->setNamespace('\Link')->group(function (RouteGroup $r): void {
            $r->get('create', '/', '@create');
            $r->post('store', '/', '@store');
            $r->get('edit', '/:id/edit', '@edit')->whereDigits(':id');
            $r->put('update', '/:id', '@update')->whereDigits(':id');
            $r->get('remove', '/:id/delete', '@remove')->whereDigits(':id');
            $r->get('remove.modal', '/:id/delete/modal', '@removeModal')->whereDigits(':id');
            $r->delete('delete', '/:id', '@delete')->whereDigits(':id');
        });
    });
});
