<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\FileManager\Controller');

R::get('filemanager.profil.admin', 'admin/user/permission/filemanager', 'FilePermissionManager@admin');
R::post('filemanager.profil.admin.check', 'admin/user/permission/filemanager', 'FilePermissionManager@adminCheck');

R::get('filemanager.profil.create', 'admin/user/permission/filemanager/create', 'Profil@create');
R::post('filemanager.profil.store', 'admin/user/permission/filemanager/store', 'Profil@store');
R::get('filemanager.profil.edit', 'admin/user/permission/filemanager/:id/edit', 'Profil@edit', [ ':id' => '\d+' ]);
R::post('filemanager.profil.update', 'admin/user/permission/filemanager/:id/edit', 'Profil@update', [ ':id' => '\d+' ]);
R::get('filemanager.profil.remove', 'admin/user/permission/filemanager/:id/delete', 'Profil@remove', [ ':id' => '\d+' ]);
R::post('filemanager.profil.delete', 'admin/user/permission/filemanager/:id/delete', 'Profil@delete', [ ':id' => '\d+' ]);

/* Affichage du filemanager complet. */
R::get('filemanager.admin', 'admin/filemanager/show', 'Manager@admin');
R::get('filemanager.public', 'filemanager/public:path', 'Manager@showPublic', [ ':path' => '(/[-\w]+){0,255}' ]);
R::get('filemanager.show', 'filemanager/show:path', 'Manager@show', [ ':path' => '(/[-\w]+){0,255}' ]);
R::get('filemanager.filter', 'filemanager/filter:path', 'Manager@filter', [ ':path' => '(/[-\w]+){0,255}' ]);

R::get('filemanager.file.show', 'filemanager/file:path:name:ext', 'File@show', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);
R::get('filemanager.file.create', 'filemanager/file:path', 'File@create', [ ':path' => '(/[-\w]+){0,255}' ]);
R::post('filemanager.file.store', 'filemanager/file:path', 'File@store', [ ':path' => '(/[-\w]+){0,255}' ]);
R::get('filemanager.file.edit', 'filemanager/file:path:name:ext/edit', 'File@edit', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w ]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);
R::post('filemanager.file.update', 'filemanager/file:path:name:ext/edit', 'File@update', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w ]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);
R::get('filemanager.file.remove', 'filemanager/file:path:name:ext/delete', 'File@remove', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w ]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);
R::post('filemanager.file.delete', 'filemanager/file:path:name:ext/delete', 'File@delete', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w ]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);
R::get('filemanager.file.download', 'filemanager/download:path:name:ext', 'File@download', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w ]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);

/* Affichage du filemanager uniquement les répertoires. */
R::get('filemanager.copy.admin', 'filemanager/copy:path:name:ext', 'FileCopy@admin', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w ]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);
R::post('filemanager.copy.update', 'filemanager/copy:path:name:ext', 'FileCopy@update', [ ':path' => '(/[-\w]+){0,255}', ':name' => '/[-\w ]{1,255}', ':ext' => '\.[a-zA-Z0-9]{1,10}' ]);
R::get('filemanager.copy.show', 'filemanager/copy:path', 'FileCopy@show', [ ':path' => '(/[-\w]+){0,255}' ]);

R::get('filemanager.folder.create', 'filemanager/folder:path/create', 'Folder@create', [ ':path' => '(/[-\w]+){0,255}' ]);
R::post('filemanager.folder.store', 'filemanager/folder:path/store', 'Folder@store', [ ':path' => '(/[-\w]+){0,255}' ]);
R::get('filemanager.folder.edit', 'filemanager/folder:path/edit', 'Folder@edit', [ ':path' => '(/[-\w]+){1,255}' ]);
R::post('filemanager.folder.update', 'filemanager/folder:path/edit', 'Folder@update', [ ':path' => '(/[-\w]+){1,255}' ]);
R::get('filemanager.folder.remove', 'filemanager/folder:path/delete', 'Folder@remove', [ ':path' => '(/[-\w]+){1,255}' ]);
R::post('filemanager.folder.delete', 'filemanager/folder:path/delete', 'Folder@delete', [ ':path' => '(/[-\w]+){1,255}' ]);
