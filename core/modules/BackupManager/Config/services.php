<?php

use Soosyze\Core\Modules\BackupManager\Extend;
use Soosyze\Core\Modules\BackupManager\Hook;
use Soosyze\Core\Modules\BackupManager\Services;

return [
    'backupmanager.extend' => [
        'class' => Extend::class,
        'hooks' => [
            'install.user' => 'hookInstallUser'
        ]
    ],
    'backupmanager' => [
        'class' => Services\BackupManager::class,
        'arguments' => [
            'maxBackups' => '#settings.max_backups',
            'root' => ROOT
        ]
    ],
    'backupmanager.hook.config' => [
        'class' => Hook\Config::class,
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'backupmanager.hook.user' => [
        'class' => Hook\User::class,
        'hooks' => [
            'user.permission.module' => 'hookUserPermissionModule',
            'route.backupmanager.admin' => 'hookBackupManage',
            'route.backupmanager.dobackup' => 'hookBackupManage',
            'route.backupmanager.download' => 'hookBackupManage',
            'route.backupmanager.restore' => 'hookBackupManage',
            'route.backupmanager.delete' => 'hookBackupManage',
            'route.backupmanager.delete.all' => 'hookBackupManage'
        ]
    ],
    'backupmanager.hook.cron' => [
        'class' => Hook\Cron::class,
        'hooks' => [
            'app.cron' => 'hookAppCron'
        ]
    ],
    'backupmanager.hook.tool' => [
        'class' => Hook\Tool::class,
        'hooks' => [
            'tools.admin' => 'hookToolAdmin'
        ]
    ]
];
