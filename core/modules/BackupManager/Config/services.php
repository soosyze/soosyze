<?php

return [
    'backupmanager.extend' => [
        'class' => 'Soosyze\Core\Modules\BackupManager\Extend',
        'hooks' => [
            'install.user' => 'hookInstallUser'
        ]
    ],
    'backupmanager' => [
        'class' => 'Soosyze\Core\Modules\BackupManager\Services\BackupManager',
        'arguments' => [
            'maxBackups' => '#settings.max_backups',
            'root' => ROOT
        ]
    ],
    'backupmanager.hook.config' => [
        'class' => 'Soosyze\Core\Modules\BackupManager\Hook\Config',
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'backupmanager.hook.user' => [
        'class' => 'Soosyze\Core\Modules\BackupManager\Hook\User',
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
        'class' => 'Soosyze\Core\Modules\BackupManager\Hook\Cron',
        'hooks' => [
            'app.cron' => 'hookAppCron'
        ]
    ],
    'backupmanager.hook.tool' => [
        'class' => 'Soosyze\Core\Modules\BackupManager\Hook\Tool',
        'hooks' => [
            'tools.admin' => 'hookToolAdmin'
        ]
    ]
];
