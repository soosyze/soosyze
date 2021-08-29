<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\User\Controller');

R::get('user.api.select', 'api/user', 'UserApi@select');

R::useNamespace('SoosyzeCore\User\Controller')->name('user.')->prefix('user')->group(function () {
    R::get('login', '/login:url', 'Login@login', [ ':url' => '(/[\d\w-]{10,})?' ]);
    R::post('login.check', '/login:url', 'Login@loginCheck', [ ':url' => '(/[\d\w-]{10,})?' ]);
    R::get('relogin', '/relogin:url', 'Login@relogin', [ ':url' => '(/[\d\w-]{10,})?' ]);
    R::post('relogin.check', '/relogin:url', 'Login@reloginCheck', [ ':url' => '(/[\d\w-]{10,})?' ]);
    R::get('reset', '/:id/reset/:token', 'Login@resetUser', [ ':id'    => '\d+', ':token' => '[\d\w-]+' ]);
    R::get('logout', '/logout', 'Login@logout');

    R::get('register.create', '/register', 'Register@create');
    R::post('register.store', '/register', 'Register@store');
    R::get('activate', '/:id/activate/:token', 'Register@activate', [ ':id'    => '\d+', ':token' => '[\d\w-]+' ]);

    R::get('account', '/account', 'User@account');
    R::get('show', '/:id', 'User@show', [ ':id' => '\d+' ]);
    R::post('store', '/', 'User@store');
    R::get('edit', '/:id/edit', 'User@edit', [ ':id' => '\d+' ]);
    R::put('update', '/:id', 'User@update', [ ':id' => '\d+' ]);
    R::get('remove', '/:id/delete', 'User@remove', [ ':id' => '\d+' ]);
    R::delete('delete', '/:id', 'User@delete', [ ':id' => '\d+' ]);
});
R::useNamespace('SoosyzeCore\User\Controller')->name('user.')->prefix('admin/user')->group(function () {
    R::get('admin', '/', 'UsersManager@admin');
    R::get('create', '/create', 'User@create');
    R::get('filter', '/filter', 'UsersManager@filter');
    R::get('filter.page', '/filter/:id', 'UsersManager@filterPage', [ ':id' => '[1-9]\d*' ]);
    R::get('permission.admin', '/permission', 'Permission@admin');
    R::put('permission.update', '/permission', 'Permission@udpate');
});
R::useNamespace('SoosyzeCore\User\Controller')->name('user.role.')->prefix('admin/user/role')->group(function () {
    R::get('admin', '/', 'RoleManager@admin');
    R::patch('admin.check', '/', 'RoleManager@adminCheck');
    R::get('create', '/create', 'Role@create');
    R::post('store', '/', 'Role@store');
    R::get('edit', '/:id/edit', 'Role@edit', [ ':id' => '\d+' ]);
    R::put('update', '/:id', 'Role@update', [ ':id' => '\d+' ]);
    R::get('remove', '/:id/delete', 'Role@remove', [ ':id' => '\d+' ]);
    R::delete('delete', '/:id', 'Role@delete', [ ':id' => '\d+' ]);
});
