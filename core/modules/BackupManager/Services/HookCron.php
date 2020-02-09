<?php

namespace SoosyzeCore\BackupManager\Services;

class HookCron
{
    private $backupservice;
    
    private $config;
    
    public function __contruct(BackupService $bs, $config)
    {
        $this->backupservice = $bs;
        $this->config = $config;
    }
    
    public function hookCron()
    {
        if ($this->config->get('settings.backup_cron')) {
            $this->backupservice->doBackup();
        }
    }
}
