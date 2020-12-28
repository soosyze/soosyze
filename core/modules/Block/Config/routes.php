<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Block\Controller');

R::get('block.section.admin', 'admin/theme/:theme/section', 'Section@admin', [ ':theme' => 'public|admin' ]);
R::post('block.section.update', 'admin/section/:id/edit', 'Section@update', [ ':id' => '\d+' ]);

R::get('block.show', 'block/:id', 'Block@show', [ ':id' => '\d+' ]);
R::get('block.create', 'block/:theme/:section', 'Block@create', [ ':theme' => 'public|admin', ':section' => '[\-a-z_]+' ]);
R::post('block.store', 'block/:theme/:section', 'Block@store', [ ':theme' => 'public|admin', ':section' => '[\-a-z_]+' ]);
R::get('block.edit', 'block/:id/edit', 'Block@edit', [ ':id' => '\d+' ]);
R::post('block.update', 'block/:id', 'Block@update', [ ':id' => '\d+' ]);
R::delete('block.delete', 'block/:id/delete', 'Block@delete', [ ':id' => '\d+' ]);
