<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Menu\Controller');

R::get('menu.show', 'admin/menu/:menu', 'Menu@show', [ ':menu' => '[a-z-]+' ]);
R::post('menu.show.check', 'admin/menu/:menu', 'Menu@showCheck', [ ':menu' => '[a-z-]+' ]);

R::get('menu.link.create', 'admin/menu/:menu/link', 'Link@create', [ ':menu' => '[a-z-]+' ]);
R::post('menu.link.store', 'admin/menu/:menu/link', 'Link@store', [ ':menu' => '[a-z-]+' ]);
R::get('menu.link.edit', 'admin/menu/:menu/link/:id/edit', 'Link@edit', [ ':menu' => '[a-z-]+', ':id' => '\d+' ]);
R::post('menu.link.update', 'admin/menu/:menu/link/:id/edit', 'Link@update', [ ':menu' => '[a-z-]+', ':id' => '\d+' ]);
R::get('menu.link.delete', 'admin/menu/:menu/link/:id/delete', 'Link@delete', [ ':menu' => '[a-z-]+', ':id' => '\d+' ]);
