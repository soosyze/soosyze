<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\BackupManager\Hook;

class User implements \Soosyze\Core\Modules\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
        $permissions[ 'Backups' ] = [
            'backups.manage' => 'Manage the backups'
        ];
    }

    public function hookBackupManage(): string
    {
        return 'backups.manage';
    }
}
