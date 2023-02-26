<?php

namespace Soosyze\Core\Modules\System\Contract;

use Soosyze\Config;

interface ConfigMigrationInterface
{
    public function upConfig(Config $config): void;
}
