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
        });

        $container->schema()->createTableIfNotExists('module_required', function (TableBuilder $table) {
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
            $container->query()->insertInto('permission', [ 'permission_id', 'permission_label' ])
                ->values([ 'system.config', 'Voir les configurations' ])
                ->values([ 'system.config.check', 'Éditer les configurations' ])
                ->values([ 'system.modules', 'Voir les modules' ])
                ->values([ 'system.modules.check', 'Éditer les modules' ])
                ->execute();

            $container->query()->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'system.config' ])
                ->values([ 3, 'system.config.check' ])
                ->values([ 3, 'system.modules' ])
                ->values([ 3, 'system.modules.check' ])
                ->execute();
        }
    }

    public function hookInstallMenu($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->insertInto('menu_link', [
                    'title_link',
                    'link',
                    'menu',
                    'weight',
                    'parent'
                ])
                ->values([
                    '<span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> Modules',
                    'admin/modules',
                    'admin-menu',
                    5,
                    -1
                ])
                ->values([
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
                ->from('permission')
                ->delete()
                ->regex('permission_id', '/^system./')
                ->execute();

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
