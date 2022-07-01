<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\BackupManager\Hook;

use Soosyze\Config;
use Soosyze\Core\Modules\BackupManager\Services\BackupManager;

class Cron
{
    /**
     * @var BackupManager
     */
    private $backupManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(BackupManager $backupManager, Config $config)
    {
        $this->backupManager = $backupManager;
        $this->config        = $config;
    }

    public function hookAppCron(): void
    {
        if ($this->config->get('settings.backup_cron')) {
            $dateFrenquency = $this->config->get('settings.backup_frequency', '1 day');
            /** @phpstan-var int $backupTime */
            $backupTime = $this->config->get('settings.backup_time', 0);

            $dateOld = (new \DateTime())
                ->setTimestamp($backupTime)
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
