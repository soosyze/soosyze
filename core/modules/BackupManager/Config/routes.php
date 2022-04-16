<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

define('FILE_PATTERN', '2[\d]{3}-(0[1-9]|1[0-2])-(0[1-9]|[12][\d]|3[01])T([01][\d]|2[0-3])-[0-5][\d]-[0-5][\d]');

RouteCollection::setNamespace('SoosyzeCore\BackupManager\Controller\BackupController')->name('backupmanager.')->prefix('/admin/tool/backupmanager')->group(function (RouteGroup $r): void {
    $r->get('admin', '/', '@admin');
    $r->get('dobackup', '/do', '@doBackup');
    $r->get('download', '/download/{file}', '@download', [ 'file' => FILE_PATTERN ]);
    $r->get('restore', '/restore/{file}', '@restore', [ 'file' => FILE_PATTERN ]);
    $r->get('delete', '/delete/{file}', '@delete', [ 'file' => FILE_PATTERN ]);
    $r->get('delete.all', '/delete/all', '@deleteAll');
});
