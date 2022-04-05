<?php

declare(strict_types=1);

namespace SoosyzeCore\QueryBuilder\Services;

use Queryflatfile\DriverInterface;

class Schema extends \Queryflatfile\Schema
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
