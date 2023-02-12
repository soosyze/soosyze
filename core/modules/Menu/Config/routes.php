<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\Menu\Controller as Ctr;

RouteCollection::name('menu.')->group(function (RouteGroup $r): void {
    $r->get('api.show', '/admin/api/menu/{menuId}', Ctr\MenuApi::class . '@show')->whereDigits('menuId');

    $r->prefix('/admin/menu')->group(function (RouteGroup $r): void {
        $r->setNamespace(Ctr\MenuManager::class)->group(function (RouteGroup $r): void {
            $r->get('admin', '/', '@show');
            $r->get('show', '/{menuId}', '@show')->whereDigits('menuId');
            $r->patch('check', '/{menuId}', '@check')->whereDigits('menuId');
        });
        $r->setNamespace(Ctr\Menu::class)->group(function (RouteGroup $r) {
            $r->get('create', '/create', '@create');
            $r->post('store', '/create', '@store');
            $r->get('edit', '/{menuId}/edit', '@edit')->whereDigits('menuId');
            $r->put('update', '/{menuId}', '@update')->whereDigits('menuId');
            $r->get('remove', '/{menuId}/delete', '@remove')->whereDigits('menuId');
            $r->delete('delete', '/{menuId}', '@delete')->whereDigits('menuId');
        });

        $r->prefix('/{menuId}/link')->withs([ 'menuId' => '\d+' ])->name('link.')->setNamespace(Ctr\Link::class)->group(function (RouteGroup $r): void {
            $r->get('create', '/', '@create');
            $r->post('store', '/', '@store');
            $r->get('edit', '/{linkId}/edit', '@edit')->whereDigits('linkId');
            $r->put('update', '/{linkId}', '@update')->whereDigits('linkId');
            $r->get('remove', '/{linkId}/delete', '@remove')->whereDigits('linkId');
            $r->get('remove.modal', '/{linkId}/delete/modal', '@removeModal')->whereDigits('linkId');
            $r->delete('delete', '/{linkId}', '@delete')->whereDigits('linkId');
        });
    });
});
