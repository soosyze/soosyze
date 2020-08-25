<?php

namespace SoosyzeCore\BackupManager\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Backups' ] = [
            'backups.manage' => t('Manage the backups')
        ];
    }

    public function hookBackupManage()
    {
        return 'backups.manage';
    }
}
