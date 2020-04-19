<?php

namespace SoosyzeCore\Node\Services;

class HookCron
{
    protected $config;

    protected $query;

    public function __construct( $config, $query )
    {
        $this->config = $config;
        $this->query  = $query;
    }

    public function hookCron()
    {
        if( $this->config->get('settings.node_cron', false) )
        {
            $this->query
                ->update('node', [ 'node_status_id' => 1 ])
                ->where('node_status_id', 2)
                ->where('date_created', '<=', time())
                ->execute();
        }
    }
}