<?php

use Soosyze\Components\Router\Route as R;

define('BLOCK_WITH_THEME', [
    ':theme' => 'public|admin'
]);
define('BLOCK_WITH', [
    ':theme' => 'public|admin',
    ':section' => '\w+'
]);
define('BLOCK_WITH_ID', [
    ':theme' => 'public|admin',
     ':id' => '\d+'
]);

R::useNamespace('SoosyzeCore\Block\Controller');

R::get('block.section.admin', 'admin/theme/:theme/section', 'Section@admin', [ ':theme' => 'public|admin' ]);
R::get('block.section.show', 'admin/section/:theme/:section', 'Section@show', BLOCK_WITH);
R::post('block.section.update', 'admin/section/:id/edit', 'Section@update', [ ':id' => '\d+' ]);

R::useNamespace('SoosyzeCore\Block\Controller')->name('block.')->prefix('block')->group(function () {
    R::get('create.list', '/:theme/create/:section', 'Block@createList', BLOCK_WITH);
    R::get('create.show', '/create/:id', 'Block@createShow', [ ':id' => '[\w\.\-]+' ]);
    R::post('create.form', '/:theme/create/form', 'Block@createForm', BLOCK_WITH_THEME);
    R::post('store', '/:theme', 'Block@store', BLOCK_WITH_THEME);

    R::get('edit', '/:theme/:id/edit', 'Block@edit', BLOCK_WITH_ID);
    R::post('update', '/:theme/:id', 'Block@update', BLOCK_WITH_ID);
    R::get('remove', '/:theme/:id/delete', 'Block@remove', BLOCK_WITH_ID);
    R::post('delete', '/:theme/:id/delete', 'Block@delete', BLOCK_WITH_ID);
});
