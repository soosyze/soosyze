<?php

namespace SoosyzeCore\System;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;

class Installer implements \SoosyzeCore\System\Migration
{
    public function getDir()
    {
        return __DIR__;
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
                ->string('controller');
            })
            ->createTableIfNotExists('module_require', function (TableBuilder $table) {
                $table->string('title_module')
                ->string('title_required')
                ->string('version');
            })
            ->createTableIfNotExists('system_alias_url', function (TableBuilder $table) {
                $table->string('source')
                ->string('alias');
            })
            ->createTableIfNotExists('migration', function (TableBuilder $table) {
                $table->string('migration')
                ->string('extension');
            });

        $ci->config()
            ->set('settings.maintenance', false)
            ->set('settings.module_update_time', '')
            ->set('settings.module_update', false)
            ->set('settings.rewrite_engine', false)
            ->set('settings.path_no_found', 'node/1')
            ->set('settings.path_index', 'node/2')
            ->set('settings.path_access_denied', 'user/login')
            ->set('settings.path_maintenance', '')
            ->set('settings.meta_title', 'Soosyze')
            ->set('settings.meta_description', 'Hello world !')
            ->set('settings.meta_keyboard', '')
            ->set('settings.favicon', '')
            ->set('settings.lang', 'en')
            ->set('settings.timezone', 'Europe/Paris');
    }

    public function seeders(ContainerInterface $ci)
    {
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallMenu($ci);
        $this->hookInstallUser($ci);
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
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
                    'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
                ])
                ->values([
                    'system.module.edit', 'fa fa-th-large', 'Modules', 'admin/modules',
                    'menu-admin', 5, -1
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
