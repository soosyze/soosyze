<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Hook;

use SoosyzeCore\Node\Hook\Config;
use SoosyzeCore\QueryBuilder\Services\Query;

class Cron
{
    /**
     * @var bool
     */
    private $nodeCron;

    /**
     * @var Query
     */
    private $query;

    public function __construct(Query $query, bool $nodeCron = Config::CRON)
    {
        $this->query    = $query;
        $this->nodeCron = $nodeCron;
    }

    public function hookCron(): void
    {
        if ($this->nodeCron) {
            $this->query
                ->update('node', [ 'node_status_id' => 1 ])
                ->where('node_status_id', '=', 2)
                ->where('date_created', '<=', time())
                ->execute();
        }
    }
}
