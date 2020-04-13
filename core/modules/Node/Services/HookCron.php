<?php

namespace SoosyzeCore\Node\Services;

class HookCron
{
    public function __construct($query)
    {
        $this->query = $query;
    }
    
    public function hookCron()
    {
        $this->query
            ->update('node', [ 'node_status_id' => 1 ])
            ->where('node_status_id', 2)
            ->where('date_created', '<=', time())
            ->execute();
    }
}
