<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\QueryBuilder\Services;

use Soosyze\Queryflatfile\DriverInterface;

class Schema extends \Soosyze\Queryflatfile\Schema
{
    public function __construct(
        ?string $host = null,
        string $name = 'schema',
        DriverInterface $driver = null
    ) {
        parent::__construct($host, $name, $driver);
        $this->root = ROOT;
    }
}
