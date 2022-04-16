<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

define('ENTITY_STORE_WITH', [
    'idNode' => '\d+',
    'entity'  => '[_a-z]+'
]);
define('ENTITY_EDIT_WITH', [
    'idNode'   => '\d+',
    'entity'    => '[_a-z]+',
    'idEntity' => '\d+'
]);

RouteCollection::setNamespace('SoosyzeCore\Node\Controller')->name('node.')->prefix('/node')->group(function (RouteGroup $r): void {
    $r->get('show', '/{idNode}', '\Node@show', [ 'idNode' => '\d+' ]);
    $r->get('status.search', '/status/search', '\NodeStatus@search');
    $r->get('type.search', '/type/search', '\NodeType@search');
    $r->get('filter', '/filter', '\NodeManager@filter');
    $r->get('filter.page', '/filter/{pageId}', '\NodeManager@filterPage', [ 'pageId' => '[1-9]\d*' ]);
});
RouteCollection::setNamespace('SoosyzeCore\Node\Controller\NodeApi')->name('node.api.')->prefix('/api/node/{idNode}')->group(function (RouteGroup $r): void {
    $r->get('remove', '/remove', '@remove', [ 'idNode' => '\d+' ]);
    $r->delete('delete', '/delete', '@delete', [ 'idNode' => '\d+' ]);
});
RouteCollection::setNamespace('SoosyzeCore\Node\Controller')->prefix('/admin/node')->name('node.')->group(function (RouteGroup $r): void {
    $r->setNamespace('\NodeManager')->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
    });
    $r->setNamespace('\Node')->group(function (RouteGroup $r): void {
        $r->get('add', '/add', '@add');
        $r->get('create', '/{node}/create', '@create', [ 'node' => '[_a-z]+' ]);
        $r->post('store', '/{node}/create', '@store', [ 'node' => '[_a-z]+' ]);
        $r->get('edit', '/{idNode}/edit', '@edit', [ 'idNode' => '\d+' ]);
        $r->put('update', '/{idNode}/edit', '@update', [ 'idNode' => '\d+' ]);
        $r->get('remove', '/{idNode}/remove', '@remove', [ 'idNode' => '\d+' ]);
        $r->delete('delete', '/{idNode}/delete', '@delete', [ 'idNode' => '\d+' ]);
    });
    $r->setNamespace('\NodeClone')->group(function (RouteGroup $r): void {
        $r->get('clone', '/{idNode}/clone', '@duplicate', [ 'idNode' => '\d+' ]);
    });
    $r->setNamespace('\Entity')->name('entity.')->prefix('/{idNode}/{entity}')->group(function (RouteGroup $r): void {
        $r->get('create', '/', '@create', ENTITY_STORE_WITH);
        $r->post('store', '/', '@store', ENTITY_STORE_WITH);
        $r->get('edit', '/{idEntity}/edit', '@edit', ENTITY_EDIT_WITH);
        $r->put('update', '/{idEntity}/edit', '@update', ENTITY_EDIT_WITH);
        $r->delete('delete', '/{idEntity}/delete', '@delete', ENTITY_EDIT_WITH);
    });
});
