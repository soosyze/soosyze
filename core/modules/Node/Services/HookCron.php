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
            ->update('node', [ 'published' => true ])
            ->where('published', false)
            ->where('date_created', '<=', time())
            ->execute();
    }
}
