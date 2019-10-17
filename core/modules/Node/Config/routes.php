<?php

use Soosyze\Route as R;

R::useNamespace('SoosyzeCore\Node\Controller');

R::get('node.index', 'admin/node', 'Node@admin');
R::get('node.add', 'admin/node/add', 'Node@add');
R::get('node.show', 'node/:id', 'Node@show', [':id' => '\d+']);
R::get('node.create', 'admin/node/add/:type', 'Node@create', [':type' => '[a-z]+']);
R::post('node.store', 'admin/node/add/:type', 'Node@store', [':type' => '[a-z]+']);
R::get('node.edit', 'admin/node/:id/edit', 'Node@edit', [':id' => '\d+']);
R::post('node.update', 'admin/node/:id/edit', 'Node@update', [':id' => '\d+']);
R::get('node.delete', 'admin/node/:id/delete', 'Node@delete', [':id' => '\d+']);
