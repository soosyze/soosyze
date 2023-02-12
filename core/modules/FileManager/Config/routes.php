<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\FileManager\Controller as Ctr;

define('FILEMANAGER_FILE_WITH', [
    'path' => '(/[\w]+){0,255}',
    'name' => '/[\w ]{1,255}',
    'ext'  => '[a-zA-Z0-9]{1,10}'
]);
define('FILEMANAGER_PATH_WITH', [ 'path' => '(/[\w]+){0,255}' ]);

RouteCollection::setNamespace(Ctr\Manager::class)->prefix('/filemanager')->name('filemanager.')->group(function (RouteGroup $r): void {
    $r->get('public', '/public{path}', '@showPublic', FILEMANAGER_PATH_WITH);
    $r->get('show', '/show{path}', '@show', FILEMANAGER_PATH_WITH);
    $r->get('filter', '/filter{path}', '@filter', FILEMANAGER_PATH_WITH);
});
RouteCollection::prefix('/filemanager')->name('filemanager.')->group(function (RouteGroup $r): void {
    $r->prefix('/file')->name('file.')->setNamespace(Ctr\File::class)->group(function (RouteGroup $r): void {
        $r->get('show', '{path}{name}-{ext}', '@show', FILEMANAGER_FILE_WITH);
        $r->get('create', '{path}', '@create', FILEMANAGER_PATH_WITH);
        $r->post('store', '{path}', '@store', FILEMANAGER_PATH_WITH);
        $r->get('edit', '{path}{name}-{ext}/edit', '@edit', FILEMANAGER_FILE_WITH);
        $r->put('update', '{path}{name}-{ext}', '@update', FILEMANAGER_FILE_WITH);
        $r->get('remove', '{path}{name}-{ext}/delete', '@remove', FILEMANAGER_FILE_WITH);
        $r->delete('delete', '{path}{name}-{ext}', '@delete', FILEMANAGER_FILE_WITH);
        $r->get('download', '{path}{name}-{ext}/download', '@download', FILEMANAGER_FILE_WITH);
    });
    /* Affichage du filemanager uniquement les répertoires. */
    $r->prefix('/copy')->name('copy.')->setNamespace(Ctr\FileCopy::class)->group(function (RouteGroup $r): void {
        $r->get('admin', '{path}{name}-{ext}', '@admin', FILEMANAGER_FILE_WITH);
        $r->post('update', '{path}{name}-{ext}', '@update', FILEMANAGER_FILE_WITH);
        $r->get('show', '{path}', '@show', FILEMANAGER_PATH_WITH);
    });
    $r->prefix('/folder{path}')->withs([ 'path' => '(/[-\w]+){1,255}' ])->name('folder.')->setNamespace('\Folder')->group(function (RouteGroup $r): void {
        $r->get('create', '/create', '@create', FILEMANAGER_PATH_WITH);
        $r->post('store', '/', '@store', FILEMANAGER_PATH_WITH);
        $r->get('edit', '/edit', '@edit');
        $r->put('update', '/', '@update');
        $r->get('remove', '/delete', '@remove');
        $r->delete('delete', '/', '@delete');
        $r->get('download', '/download', '@download');
    });
});
RouteCollection::prefix('/admin')->name('filemanager.')->group(function (RouteGroup $r): void {
    /* Affichage du filemanager complet. */
    $r->setNamespace(Ctr\Manager::class)->group(function (RouteGroup $r): void {
        $r->get('admin', '/filemanager/show', '@admin');
    });
    $r->prefix('/user/permission/filemanager')->name('permission.')->group(function (RouteGroup $r): void {
        $r->setNamespace(Ctr\FilePermissionManager::class)->group(function (RouteGroup $r): void {
            $r->get('admin', '/', '@admin');
            $r->patch('admin.check', '/', '@adminCheck');
        });
        $r->setNamespace(Ctr\FilePermission::class)->group(function (RouteGroup $r): void {
            $r->get('create', '/create', '@create');
            $r->post('store', '/', '@store');
            $r->get('edit', '/{id}/edit', '@edit')->whereDigits('id');
            $r->put('update', '/{id}', '@update')->whereDigits('id');
            $r->get('remove', '/{id}/delete', '@remove')->whereDigits('id');
            $r->delete('delete', '/{id}', '@delete')->whereDigits('id');
        });
    });
});
