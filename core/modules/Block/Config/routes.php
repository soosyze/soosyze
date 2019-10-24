<?php

use Soosyze\Route as R;

R::useNamespace('SoosyzeCore\Block\Controller');

R::get('section.admin', 'admin/section/:theme', 'Section@admin', [ ':theme' => 'theme_admin|theme' ]);
R::post('section.update', 'admin/section/:id/edit', 'Section@update', [ ':id' => '\d+' ]);

R::get('block.show', 'block/:id', 'Block@show', [ ':id' => '\d+' ]);
R::get('block.create', 'block/:section', 'Block@create', [ ':section' => '[a-z-_]+' ]);
R::post('block.store', 'block/:section', 'Block@store', [ ':section' => '[a-z-_]+' ]);
R::get('block.edit', 'block/:id/edit', 'Block@edit', [ ':id' => '\d+' ]);
R::post('block.update', 'block/:id', 'Block@update', [ ':id' => '\d+' ]);
R::post('block.delete', 'block/:id/delete', 'Block@delete', [ ':id' => '\d+' ]);
