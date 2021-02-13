<?php

use Soosyze\Config;

return [
    'up_config' => function (Config $config) {
        $config->set('settings.new_title', 'Articles');
    }
];
