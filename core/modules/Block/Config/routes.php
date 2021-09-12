<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\Block\Controller as Ctr;

define('BLOCK_WITHS_THEME', [
    'theme' => 'public|admin'
]);

RouteCollection::name('block.')->group(function (RouteGroup $r): void {
    $r->setNamespace(Ctr\Section::class)->name('section.')->prefix('/admin')->withs(BLOCK_WITHS_THEME)->group(function (RouteGroup $r): void {
        $r->get('admin', '/theme/{theme}/section', '@admin');
        $r->post('update', '/section/{id}/edit', '@update')->whereDigits('id');
    });
    $r->setNamespace(Ctr\Block::class)->prefix('/block')->withs(BLOCK_WITHS_THEME)->group(function (RouteGroup $r): void {
        $r->get('create.list', '/{theme}/create/{section}', '@createList')->whereWords('section');
        $r->get('create.show', '/create/{id}', '@createShow', [ 'id' => '[\w\-]+' ]);
        $r->post('create.form', '/{theme}/create/form', '@createForm');

        $r->post('store', '/{theme}', '@store');
        $r->get('edit', '/{theme}/{id}/edit', '@edit')->whereDigits('id');
        $r->put('update', '/{theme}/{id}', '@update')->whereDigits('id');
        $r->get('remove', '/{theme}/{id}/delete', '@remove')->whereDigits('id');
        $r->delete('delete', '/{theme}/{id}', '@delete')->whereDigits('id');
    });
    $r->setNamespace('\Style')->name('style.')->withs(BLOCK_WITHS_THEME)->group(function (RouteGroup $r): void {
        $r->get('edit', '/{theme}/{id}/style', '@edit')->whereDigits('id');
        $r->put('update', '/{theme}/{id}/style', '@update')->whereDigits('id');
    });
    $r->get('tool.style', '/admin/tool/style', '\Style@styleGenerate');
});
