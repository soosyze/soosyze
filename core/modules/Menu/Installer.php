<?php

namespace SoosyzeCore\Menu;

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
                ->string('title_link')
                ->string('target_link')->valueDefault('_self')
                ->string('menu')
                ->integer('weight')->valueDefault(1)
                ->integer('parent')
                ->boolean('active')->valueDefault(true);
            });
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('menu', [ 'name', 'title', 'description' ])
            ->values([ 'menu-admin', 'Menu d’administration', 'Le menu pour la gestion du site.' ])
            ->values([ 'menu-main', 'Menu principal', 'Le menu principal du site utilisable pour les internautes.' ])
            ->values([ 'menu-user', 'Menu utilisateur', 'Le menu des liens utilisateurs (compte, connexion...).' ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                null, null, 'Site de Soosyze', 'https:\\soosyze.com', 'menu-main', 10, -1
            ])
            ->values([
                'menu.show', 'fa fa-bars', 'Menu', 'menu/menu-main', 'menu-admin', 3, -1
            ])
            ->execute();
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallUser($ci);
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'menu.administer' ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->schema()->dropTable('menu_link');
        $ci->schema()->dropTable('menu');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'menu.%')
                ->execute();
        }
    }
}
