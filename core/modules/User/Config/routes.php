<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\User\Controller');

R::get('user.login', 'user/login:url', 'Login@login', [ ':url' => '(/[\d\w-]{10,})?' ]);
R::post('user.login.check', 'user/login:url', 'Login@loginCheck', [ ':url' => '(/[\d\w-]{10,})?' ]);
R::get('user.relogin', 'user/relogin:url', 'Login@relogin', [ ':url' => '(/[\d\w-]{10,})?' ]);
R::post('user.relogin.check', 'user/relogin:url', 'Login@reloginCheck', [ ':url' => '(/[\d\w-]{10,})?' ]);
R::get('user.reset', 'user/:id/reset/:token', 'Login@resetUser', [ ':id' => '\d+', ':token' => '[\d\w-]+' ]);
R::get('user.logout', 'user/logout', 'Login@logout');

R::get('user.permission.admin', 'admin/user/permission', 'Permission@admin');
R::post('user.permission.update', 'admin/user/permission', 'Permission@udpate');

R::get('user.register.create', 'user/register', 'Register@create');
R::post('user.register.store', 'user/register', 'Register@store');
R::get('user.activate', 'user/:id/activate/:token', 'Register@activate', [ ':id' => '\d+', ':token' => '[\d\w-]+']);

/* Page de gestion des rôles */
R::get('user.role.admin', 'admin/user/role', 'RoleManager@admin');
R::post('user.role.admin.check', 'admin/user/role', 'RoleManager@adminCheck');
/* Rôles utilisateur */
R::get('user.role.create', 'admin/user/role/create', 'Role@create');
R::post('user.role.store', 'admin/user/role/create', 'Role@store');
R::get('user.role.edit', 'admin/user/role/:id/edit', 'Role@edit', [ ':id' => '\d+' ]);
R::post('user.role.update', 'admin/user/role/:id/edit', 'Role@update', [ ':id' => '\d+' ]);
R::get('user.role.remove', 'admin/user/role/:id/delete', 'Role@remove', [ ':id' => '\d+' ]);
R::post('user.role.delete', 'admin/user/role/:id/delete', 'Role@delete', [ ':id' => '\d+' ]);

R::get('user.account', 'user/account', 'User@account');
R::get('user.show', 'user/:id', 'User@show', [ ':id' => '\d+' ]);
R::get('user.create', 'admin/user/create', 'User@create');
R::post('user.store', 'user', 'User@store');
R::get('user.edit', 'user/:id/edit', 'User@edit', [ ':id' => '\d+' ]);
R::post('user.update', 'user/:id/edit', 'User@update', [ ':id' => '\d+' ]);
R::get('user.remove', 'user/:id/delete', 'User@remove', [ ':id' => '\d+' ]);
R::post('user.delete', 'user/:id/delete', 'User@delete', [ ':id' => '\d+' ]);

R::get('user.admin', 'admin/user', 'UsersManager@admin');
R::get('user.filter', 'user/filter', 'UsersManager@filter');
