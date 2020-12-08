<?php

namespace SoosyzeCore\Menu;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;

class Installer extends \SoosyzeCore\System\Migration
{
    public function getDir()
    {
        return __DIR__;
    }

    public function boot()
    {
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/main.json');
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/permission.json');
    }

    public function install(ContainerInterface $ci)
    {
        $ci->schema()
            ->createTableIfNotExists('menu', function (TableBuilder $table) {
                $table->string('name')
                ->string('title')
                ->text('description');
            })
            ->createTableIfNotExists('menu_link', function (TableBuilder $table) {
                $table->increments('id')
                ->string('key')->nullable()
                ->string('icon')->nullable()
                ->string('link')
                ->string('link_router')->nullable()
                ->string('query')->nullable()
                ->string('fragment')->nullable()
                ->string('title_link')
                ->boolean('target_link')->valueDefault(false)
                ->string('menu')
                ->integer('weight')->valueDefault(1)
                ->integer('parent')
                ->boolean('has_children')->valueDefault(false)
                ->boolean('active')->valueDefault(true);
            });

        $ci->query()
            ->insertInto('menu', [ 'name', 'title', 'description' ])
            ->values([ 'menu-admin', 'Administration menu', 'Menu for the management of the site' ])
            ->values([ 'menu-main', 'Main Menu', 'Main menu of the site' ])
            ->values([ 'menu-user', 'User Menu', 'User links menu' ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                'menu.admin', 'fa fa-bars', 'Menu', 'admin/menu', 'menu-admin',
                3, -1
            ])
            ->execute();
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('menu_link', [
                'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent',
                'target_link'
            ])
            ->values([
                null, null, 'Soosyze website', 'https://soosyze.com', 'menu-main',
                50, -1, true
            ])
            ->execute();
    }

    public function hookInstall(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $this->hookInstallUser($ci);
        }
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'menu.administer' ])
            ->execute();
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->schema()->dropTable('menu_link');
        $ci->schema()->dropTable('menu');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        if ($ci->module()->has('Block')) {
            $this->hookUninstallBlock($ci);
        }
        if ($ci->module()->has('User')) {
            $this->hookUninstallUser($ci);
        }
    }

    public function hookUninstallBlock(ContainerInterface $ci)
    {
        $ci->query()
            ->from('block')
            ->delete()
            ->where('hook', 'like', 'menu.%')
            ->execute();
    }

    public function hookUninstallUser(ContainerInterface $ci)
    {
        $ci->query()
            ->from('role_permission')
            ->delete()
            ->where('permission_id', 'like', 'menu.%')
            ->execute();
    }
}
