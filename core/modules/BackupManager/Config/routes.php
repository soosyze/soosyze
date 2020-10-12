<?php

use Soosyze\Components\Router\Route as R;

define('FILE_PATTERN', '2[\d]{3}-(0[1-9]|1[0-2])-(0[1-9]|[12][\d]|3[01])T([01][\d]|2[0-3])-[0-5][\d]-[0-5][\d]');

R::useNamespace('SoosyzeCore\BackupManager\Controller');

R::get('backupmanager.admin', 'admin/backupmanager', 'BackupController@admin');
R::get('backupmanager.dobackup', 'admin/backupmanager/do', 'BackupController@doBackup');
R::get('backupmanager.download', 'admin/backupmanager/download/:file', 'BackupController@download', [
    ':file' => FILE_PATTERN
]);
R::get('backupmanager.restore', 'admin/backupmanager/restore/:file', 'BackupController@restore', [
    ':file' => FILE_PATTERN
]);
R::get('backupmanager.delete', 'admin/backupmanager/delete/:file', 'BackupController@delete', [
    ':file' => FILE_PATTERN
]);
R::get('backupmanager.delete.all', 'admin/backupmanager/delete/all', 'BackupController@deleteAll');
