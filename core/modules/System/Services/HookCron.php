<?php

namespace SoosyzeCore\System\Services;

class HookCron
{
    private $config;

    private $migration;

    private $schema;

    public function __construct($config, $migration, $schema)
    {
        $this->config    = $config;
        $this->migration = $migration;
        $this->schema    = $schema;
    }

    public function hookCron()
    {
        if ($this->config->get('settings.module_update_time')) {
            return;
        }
        $this->config
            ->set('settings.node_cron', false)
            ->set('settings.node_default_url', ':node_type/:node_title')
            ->set('settings.path_maintenance', '');

        $this->schema->createTableIfNotExists('migration', function ($table) {
            $table->string('migration')
                ->string('extension');
        });

        $this->migration->migrate();
    }
}
