<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

define('ENTITY_STORE_WITH', [
    ':id_node' => '\d+',
    ':entity'  => '[_a-z]+'
]);
define('ENTITY_EDIT_WITH', [
    ':id_node'   => '\d+',
    ':entity'    => '[_a-z]+',
    ':id_entity' => '\d+'
]);

RouteCollection::setNamespace('SoosyzeCore\Node\Controller')->name('node.')->prefix('/node')->group(function (RouteGroup $r): void {
    $r->get('show', '/:id_node', '\Node@show', [ ':id_node' => '\d+' ]);
    $r->get('status.search', '/status/search', '\NodeStatus@search');
    $r->get('type.search', '/type/search', '\NodeType@search');
    $r->get('filter', '/filter', '\NodeManager@filter');
    $r->get('filter.page', '/filter/:id', '\NodeManager@filterPage', [ ':id' => '[1-9]\d*' ]);
});
RouteCollection::setNamespace('SoosyzeCore\Node\Controller\NodeApi')->name('node.api.')->prefix('/api/node/:id_node')->group(function (RouteGroup $r): void {
    $r->get('remove', '/remove', '@remove', [ ':id_node' => '\d+' ]);
    $r->delete('delete', '/delete', '@delete', [ ':id_node' => '\d+' ]);
});
RouteCollection::setNamespace('SoosyzeCore\Node\Controller')->prefix('/admin/node')->name('node.')->group(function (RouteGroup $r): void {
    $r->setNamespace('\NodeManager')->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
    });
    $r->setNamespace('\Node')->group(function (RouteGroup $r): void {
        $r->get('add', '/add', '@add');
        $r->get('create', '/:node/create', '@create', [ ':node' => '[_a-z]+' ]);
        $r->post('store', '/:node/create', '@store', [ ':node' => '[_a-z]+' ]);
        $r->get('edit', '/:id_node/edit', '@edit', [ ':id_node' => '\d+' ]);
        $r->put('update', '/:id_node/edit', '@update', [ ':id_node' => '\d+' ]);
        $r->get('remove', '/:id_node/remove', '@remove', [ ':id_node' => '\d+' ]);
        $r->delete('delete', '/:id_node/delete', '@delete', [ ':id_node' => '\d+' ]);
    });
    $r->setNamespace('\NodeClone')->group(function (RouteGroup $r): void {
        $r->get('clone', '/:id_node/clone', '@duplicate', [ ':id_node' => '\d+' ]);
    });
    $r->setNamespace('\Entity')->name('entity.')->prefix('/:id_node/:entity')->group(function (RouteGroup $r): void {
        $r->get('create', '/', '@create', ENTITY_STORE_WITH);
        $r->post('store', '/', '@store', ENTITY_STORE_WITH);
        $r->get('edit', '/:id_entity/edit', '@edit', ENTITY_EDIT_WITH);
        $r->put('update', '/:id_entity/edit', '@update', ENTITY_EDIT_WITH);
        $r->delete('delete', '/:id_entity/delete', '@delete', ENTITY_EDIT_WITH);
    });
});
