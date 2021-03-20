<?php

use Soosyze\Config;

return [
    'up_config' => function (Config $config) {
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
];
