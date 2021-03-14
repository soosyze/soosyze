<?php

use Soosyze\Config;

return [
    'up_config' => function (Config $config) {
        $config->set('settings.copy_link_file', 1);
    }
];
