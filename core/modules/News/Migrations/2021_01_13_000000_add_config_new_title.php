<?php

use Soosyze\Config;
use Soosyze\Core\Modules\System\Contract\ConfigMigrationInterface;

return new class implements ConfigMigrationInterface {
    public function upConfig(Config $config): void
    {
        $config->set('settings.new_title', 'Articles');
    }
};
