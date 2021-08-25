<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Menu\Controller');

R::get('menu.api.show', 'admin/api/menu/:menu', 'MenuApi@show', [ ':menu' => '[a-z\d-]+' ]);

R::useNamespace('SoosyzeCore\Menu\Controller')->name('menu.')->prefix('admin/menu')->group(function () {
    R::get('create', '/create', 'Menu@create');
    R::post('store', '/create', 'Menu@store');
    R::get('edit', '/:menu/edit', 'Menu@edit', [ ':menu' => '[a-z\d-]+' ]);
    R::put('update', '/:menu', 'Menu@update', [ ':menu' => '[a-z\d-]+' ]);
    R::get('remove', '/:menu/delete', 'Menu@remove', [ ':menu' => '[a-z\d-]+' ]);
    R::delete('delete', '/:menu', 'Menu@delete', [ ':menu' => '[a-z\d-]+' ]);
});
R::useNamespace('SoosyzeCore\Menu\Controller')->name('menu.')->prefix('admin/menu')->group(function () {
    R::get('admin', '/', 'MenuManager@admin');
    R::get('show', '/:menu', 'MenuManager@show', [ ':menu' => '[a-z\d-]+' ]);
    R::patch('check', '/:menu', 'MenuManager@check', [ ':menu' => '[a-z\d-]+' ]);
});
R::useNamespace('SoosyzeCore\Menu\Controller')->name('menu.link.')->prefix('admin/menu/:menu/link')->group(function () {
    R::get('create', '/', 'Link@create', [ ':menu' => '[a-z\d-]+' ]);
    R::post('store', '/', 'Link@store', [ ':menu' => '[a-z\d-]+' ]);
    R::get('edit', '/:id/edit', 'Link@edit', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
    R::put('update', '/:id', 'Link@update', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
    R::get('remove', '/:id/delete', 'Link@remove', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
    R::get('remove.modal', '/:id/delete/modal', 'Link@removeModal', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
    R::delete('delete', '/:id', 'Link@delete', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
});
