<?php

namespace SoosyzeCore\BackupManager\Hook;

class User
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Backups' ] = [
            'backups.manage' => 'Manage the backups'
        ];
    }

    public function hookBackupManage()
    {
        return 'backups.manage';
    }
}
