<?php

namespace Menu;

use Queryflatfile\TableBuilder;

class Install
{
    public function install($container)
    {
        $container->schema()->createTableIfNotExists('menu', function (TableBuilder $table) {
            $table->string('name')
                ->string('title')
                ->text('description');
        });
        $container->schema()->createTableIfNotExists('menu_link', function (TableBuilder $table) {
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
        $container->query()->insertInto('menu', [ 'name', 'title', 'description' ])
            ->values([ 'admin-menu', 'Menu d’administration', 'Le menu pour la gestion du site.' ])
            ->values([ 'main-menu', 'Menu principal', 'Le menu principal du site utilisable pour les internautes.' ])
            ->values([ 'user-menu', 'Menu utilisateur', 'Le menu des liens utilisateurs (compte, connexion...).' ])
            ->execute();

        $container->query()->insertInto('menu_link', [ 'key', 'title_link', 'link',
                'menu', 'weight', 'parent' ])
            ->values([
                'node.show',
                '<span class="glyphicon glyphicon-home" aria-hidden="true"></span> Accueil',
                '/',
                'admin-menu',
                1,
                -1
            ])
            ->values([
                'menu.show',
                '<span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span> Menu',
                'menu/main-menu',
                'admin-menu',
                3,
                -1
            ])
            ->values([
                'node.show',
                'Accueil',
                '/',
                'main-menu',
                1,
                -1
            ])
            ->execute();
    }

    public function hookInstall($container)
    {
        $this->hookInstallUser($container);
    }

    public function hookInstallUser($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'menu.administer' ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->from('role_permission')
                ->delete()
                ->regex('permission_id', '/^menu./')
                ->execute();
        }

        $container->schema()->dropTable('menu_link');
        $container->schema()->dropTable('menu');
    }
}
