<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Node\Controller');

R::get('node.index', 'admin/node', 'Node@admin');
R::get('node.add', 'admin/node/add', 'Node@add');
R::get('node.show', 'node/:id_node', 'Node@show', [':id_node' => '\d+']);
R::get('node.create', 'admin/node/:node/create', 'Node@create', [':node' => '[_a-z]+']);
R::post('node.store', 'admin/node/:node/create', 'Node@store', [':node' => '[_a-z]+']);
R::get('node.edit', 'admin/node/:id_node/edit', 'Node@edit', [':id_node' => '\d+']);
R::post('node.update', 'admin/node/:id_node/edit', 'Node@update', [':id_node' => '\d+']);
R::get('node.delete', 'admin/node/:id_node/delete', 'Node@delete', [':id_node' => '\d+']);

R::get('entity.create', 'admin/node/:id_node/:entity', 'Entity@create', [':id_node' => '\d+', ':entity' => '[_a-z]+']);
R::post('entity.store', 'admin/node/:id_node/:entity', 'Entity@store', [':id_node' => '\d+', ':entity' => '[_a-z]+']);
R::get('entity.edit', 'admin/node/:id_node/:entity/:id_entity/edit', 'Entity@edit', [':id_node' => '\d+', ':entity' => '[_a-z]+', ':id_entity' => '\d+']);
R::post('entity.update', 'admin/node/:id_node/:entity/:id_entity/edit', 'Entity@update', [':id_node' => '\d+', ':entity' => '[_a-z]+', ':id_entity' => '\d+']);
R::get('entity.delete', 'admin/node/:id_node/:entity/:id_entity/delete', 'Entity@delete', [':id_node' => '\d+', ':entity' => '[_a-z]+', ':id_entity' => '\d+']);
