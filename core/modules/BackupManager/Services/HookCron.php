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
            $dateFrenquency = $this->config->get('settings.backup_frequency', '1 day');

            $dateOld = (new \DateTime())
                ->setTimestamp($this->config->get('settings.backup_time', 0))
                ->modify('+' . $dateFrenquency)
                ->getTimestamp();

            if ($dateOld > time()) {
                return;
            }

            $this->backupManager->doBackup();
            $this->config->set('settings.backup_time', time());
        }
    }
}
