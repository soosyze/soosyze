<?php

namespace SoosyzeCore\System\Services;

class HookCron
{
    public function __construct($schema, $migration, $config)
    {
        $this->schema    = $schema;
        $this->migration = $migration;
        $this->config    = $config;
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
