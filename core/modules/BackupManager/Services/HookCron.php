<?php

namespace SoosyzeCore\BackupManager\Services;

class HookCron
{
    private $backupManager;

    private $config;

    public function __construct(BackupManager $backupManager, $config)
    {
        $this->backupManager = $backupManager;
        $this->config        = $config;
    }

    public function hookAppCron()
    {
        if ($this->config->get('settings.backup_cron')) {
            $this->backupManager->doBackup();
        }
    }
}
