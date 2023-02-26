<?php

use Soosyze\Config;
use Soosyze\Core\Modules\System\Contract\ConfigMigrationInterface;
use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface, ConfigMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
    }

    public function upConfig(Config $config): void
    {
        $email = $config->get('settings.email');

        $config->set('mailer.email', $email)
            ->set('mailer.driver', 'mail')
            ->set('mailer.smtp_host', '')
            ->set('mailer.smtp_port', 0)
            ->set('mailer.smtp_encryption', 'none')
            ->set('mailer.smtp_username', '')
            ->set('mailer.smtp_password', '');

        $config->del('settings.email');
    }
};
