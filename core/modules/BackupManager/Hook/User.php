<?php

namespace SoosyzeCore\BackupManager\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'Backups' ] = [
            'backups.manage' => 'Manage the backups'
        ];
    }

    public function hookBackupManage()
    {
        return 'backups.manage';
    }
}
