<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\BackupManager\Controller');

R::get('backupmanager.index', 'admin/backupmanager/backups', 'BackupController@index');
R::post('backupmanager.dobackup', 'admin/backupmanager/do', 'BackupController@doBackup');
R::get('backupmanager.restore', 'admin/backupmanager/restore/:file', 'BackupController@restore', [':file' => '[0-9]{8}-[0-9]{6}']);
R::get('backupmanager.delete', 'admin/backupmanager/delete/:file', 'BackupController@delete', [':file' => '[0-9]{8}-[0-9]{6}']);
