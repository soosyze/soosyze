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
            ->values([ 'admin-menu', 'Menu d’administration', 'Le menu pour la gestion du site.' ])
            ->values([ 'main-menu', 'Menu principal', 'Le menu principal du site utilisable pour les internautes.' ])
            ->values([ 'user-menu', 'Menu utilisateur', 'Le menu des liens utilisateurs (compte, connexion...).' ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                'node.show', '<i class="fa fa-home" aria-hidden="true"></i> Accueil', '/', 'admin-menu', 1, -1
            ])
            ->values([
                'menu.show', '<i class="fa fa-bars" aria-hidden="true"></i> Menu', 'menu/main-menu', 'admin-menu', 3, -1
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
