<?php

namespace SoosyzeCore\System;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;

class Installer implements \SoosyzeCore\System\Migration
{
    public function getComposer()
    {
        return __DIR__ . '/composer.json';
    }

    public function install(ContainerInterface $ci)
    {
        $ci->schema()
            ->createTableIfNotExists('module_active', function (TableBuilder $table) {
                $table->string('title')
                ->string('version');
            })
            ->createTableIfNotExists('module_controller', function (TableBuilder $table) {
                $table->string('title')
                ->string('key_controller')
                ->string('controller');
            })
            ->createTableIfNotExists('module_require', function (TableBuilder $table) {
                $table->string('title_module')
                ->string('title_required')
                ->string('version');
            });

        $ci->config()
            ->set('settings.maintenance', '')
            ->set('settings.rewrite_engine', '')
            ->set('settings.path_no_found', 'node/1')
            ->set('settings.path_index', 'node/2')
            ->set('settings.path_access_denied', 'user/login')
            ->set('settings.title', 'Soosyze')
            ->set('settings.description', 'Hello world !')
            ->set('settings.keyboard', '')
            ->set('settings.favicon', '')
            ->set('settings.timezone', 'Europe/Paris');
    }

    public function seeders(ContainerInterface $ci)
    {
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallUser($ci);
        $this->hookInstallMenu($ci);
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'system.config.manage' ])
                ->values([ 3, 'system.module.manage' ])
                ->values([ 3, 'system.config.maintenance' ])
                ->execute();
        }
    }

    public function hookInstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->insertInto('menu_link', [
                    'key', 'title_link', 'link', 'menu', 'weight', 'parent'
                ])
                ->values([
                    'system.module.edit',
                    '<i class="fa fa-th-large" aria-hidden="true"></i> Modules',
                    'admin/modules',
                    'menu-admin',
                    5,
                    -1
                ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->schema()->dropTable('module_controller');
        $ci->schema()->dropTable('module_active');
        $ci->schema()->dropTable('module_required');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->from('menu_link')
                ->delete()
                ->where('link', 'admin/modules')
                ->execute();
        }
    }
    
    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'system.%')
                ->execute();
        }
    }
}
