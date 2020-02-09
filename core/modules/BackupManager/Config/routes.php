<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\BackupManager\Controller');

R::get('backupmanager.index', 'admin/backupmanager/backups', 'BackupController@index');
R::get('backupmanager.dobackup', 'admin/backupmanager/do', 'BackupController@doBackup');
R::get('backupmanager.download', 'admin/backupmanager/download/:file', 'BackupController@download', [':file' => '[0-9|\-|T|\+]+']);
R::get('backupmanager.restore', 'admin/backupmanager/restore/:file', 'BackupController@restore', [':file' => '[0-9|\-|T|\+]+']);
R::get('backupmanager.delete', 'admin/backupmanager/delete/:file', 'BackupController@delete', [':file' => '[0-9|\-|T|\+]+']);
R::get('backupmanager.delete.all', 'admin/backupmanager/delete/all', 'BackupController@deleteAll');
