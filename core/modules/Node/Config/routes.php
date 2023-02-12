<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\Node\Controller as Ctr;

define('ENTITY_STORE_WITH', [
    'idNode' => '\d+',
    'entity'  => '[_a-z]+'
]);
define('ENTITY_EDIT_WITH', [
    'idNode'   => '\d+',
    'entity'    => '[_a-z]+',
    'idEntity' => '\d+'
]);

RouteCollection::name('node.')->prefix('/node')->group(function (RouteGroup $r): void {
    $r->get('show', '/{idNode}', Ctr\Node::class . '@show', [ 'idNode' => '\d+' ]);
    $r->get('status.search', '/status/search', Ctr\NodeStatus::class . '@search');
    $r->get('type.search', '/type/search', Ctr\NodeType::class . '@search');
    $r->get('filter', '/filter', Ctr\NodeManager::class . '@filter');
    $r->get('filter.page', '/filter/{pageId}', Ctr\NodeManager::class . '@filter', [ 'pageId' => '[1-9]\d*' ]);
});
RouteCollection::setNamespace(Ctr\NodeApi::class)->name('node.api.')->prefix('/api/node/{idNode}')->group(function (RouteGroup $r): void {
    $r->get('remove', '/remove', '@remove', [ 'idNode' => '\d+' ]);
    $r->delete('delete', '/delete', '@delete', [ 'idNode' => '\d+' ]);
});
RouteCollection::prefix('/admin/node')->name('node.')->group(function (RouteGroup $r): void {
    $r->setNamespace(Ctr\NodeManager::class)->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
    });
    $r->setNamespace(Ctr\Node::class)->group(function (RouteGroup $r): void {
        $r->get('add', '/add', '@add');
        $r->get('create', '/{nodeType}/create', '@create', [ 'nodeType' => '[_a-z]+' ]);
        $r->post('store', '/{nodeType}/create', '@store', [ 'nodeType' => '[_a-z]+' ]);
        $r->get('edit', '/{idNode}/edit', '@edit', [ 'idNode' => '\d+' ]);
        $r->put('update', '/{idNode}/edit', '@update', [ 'idNode' => '\d+' ]);
        $r->get('remove', '/{idNode}/remove', '@remove', [ 'idNode' => '\d+' ]);
        $r->delete('delete', '/{idNode}/delete', '@delete', [ 'idNode' => '\d+' ]);
    });
    $r->setNamespace(Ctr\NodeClone::class)->group(function (RouteGroup $r): void {
        $r->get('clone', '/{idNode}/clone', '@duplicate', [ 'idNode' => '\d+' ]);
    });
    $r->setNamespace(Ctr\Entity::class)->name('entity.')->prefix('/{idNode}/{entity}')->group(function (RouteGroup $r): void {
        $r->get('create', '/', '@create', ENTITY_STORE_WITH);
        $r->post('store', '/', '@store', ENTITY_STORE_WITH);
        $r->get('edit', '/{idEntity}/edit', '@edit', ENTITY_EDIT_WITH);
        $r->put('update', '/{idEntity}/edit', '@update', ENTITY_EDIT_WITH);
        $r->delete('delete', '/{idEntity}/delete', '@delete', ENTITY_EDIT_WITH);
    });
});
