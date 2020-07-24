<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Menu\Controller');

R::get('menu.index', 'admin/menu', 'Menu@index');
R::get('menu.create', 'admin/menu/create', 'Menu@create');
R::post('menu.store', 'admin/menu/create', 'Menu@store');
R::get('menu.show', 'admin/menu/:menu', 'Menu@show', [ ':menu' => '[a-z\d-]+' ]);
R::post('menu.check', 'admin/menu/:menu', 'Menu@check', [ ':menu' => '[a-z\d-]+' ]);
R::get('menu.edit', 'admin/menu/:menu/edit', 'Menu@edit', [ ':menu' => '[a-z\d-]+' ]);
R::post('menu.update', 'admin/menu/:menu/edit', 'Menu@update', [ ':menu' => '[a-z\d-]+' ]);
R::get('menu.remove', 'admin/menu/:menu/delete', 'Menu@remove', [ ':menu' => '[a-z\d-]+' ]);
R::post('menu.delete', 'admin/menu/:menu/delete', 'Menu@delete', [ ':menu' => '[a-z\d-]+' ]);

R::get('menu.link.create', 'admin/menu/:menu/link', 'Link@create', [ ':menu' => '[a-z\d-]+' ]);
R::post('menu.link.store', 'admin/menu/:menu/link', 'Link@store', [ ':menu' => '[a-z\d-]+' ]);
R::get('menu.link.edit', 'admin/menu/:menu/link/:id/edit', 'Link@edit', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
R::post('menu.link.update', 'admin/menu/:menu/link/:id/edit', 'Link@update', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
R::get('menu.link.delete', 'admin/menu/:menu/link/:id/delete', 'Link@delete', [ ':menu' => '[a-z\d-]+', ':id' => '\d+' ]);
