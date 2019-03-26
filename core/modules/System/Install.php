<?php

namespace System;

use Queryflatfile\TableBuilder;

class Install
{
    public function install($container)
    {
        $container->schema()->createTableIfNotExists('module', function (TableBuilder $table) {
            $table->string('name')
                ->string('key_controller')
                ->string('controller')
                ->string('version')
                ->text('description')
                ->string('package')
                ->boolean('locked');
        })->createTableIfNotExists('module_required', function (TableBuilder $table) {
            $table->string('name_module')
                ->string('name_required');
        });

        $container->config()->set('settings.maintenance', '');
        $container->config()->set('settings.path_no_found', 'node/1');
        $container->config()->set('settings.path_index', 'node/2');
        $container->config()->set('settings.path_access_denied', 'user/login');
        $container->config()->set('settings.title', 'Soosyze');
        $container->config()->set('settings.description', 'Hello world !');
        $container->config()->set('settings.keyboard', '');
        $container->config()->set('settings.favicon', '');
        $container->config()->set('settings.timezone', 'Europe/Paris');
    }

    public function hookInstall($container)
    {
        $this->hookInstallUser($container);
        $this->hookInstallMenu($container);
    }

    public function hookInstallUser($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'system.config.manage' ])
                ->values([ 3, 'system.module.manage' ])
                ->values([ 3, 'system.config.maintenance' ])
                ->execute();
        }
    }

    public function hookInstallMenu($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->insertInto('menu_link', [
                    'key',
                    'title_link',
                    'link',
                    'menu',
                    'weight',
                    'parent'
                ])
                ->values([
                    'system.module.edit',
                    '<span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> Modules',
                    'admin/modules',
                    'admin-menu',
                    5,
                    -1
                ])
                ->values([
                    'system.config.edit',
                    '<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Configuration',
                    'admin/config',
                    'admin-menu',
                    6,
                    -1
                ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->from('role_permission')
                ->delete()
                ->regex('permission_id', '/^system./')
                ->execute();
        }

        if ($container->schema()->hasTable('menu')) {
            $container->query()
                ->from('menu_link')
                ->delete()
                ->where('link', 'admin/modules')
                ->orWhere('link', 'admin/config')
                ->execute();
        }

        $container->schema()->dropTable('module');
        $container->schema()->dropTable('module_required');
    }
}
