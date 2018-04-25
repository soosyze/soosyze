<?php

namespace Menu;

use \Queryflatfile\TableBuilder;

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
                ->string('title_link')
                ->string('target_link')
                ->string('menu')
                ->integer('weight')
                ->integer('parent')
                ->boolean('active');
        });
        $container->query()->insertInto('menu', [ 'name', 'title', 'description' ])
            ->values([ 'admin-menu', 'Menu d\'administration', 'Le menu pour la gestion du site.' ])
            ->values([ 'main-menu', 'Menu principal', 'Le menu principal du site utilisable pour les internautes.' ])
            ->values([ 'user-menu', 'Menu utilisateur', 'Le menu des liens utilisateurs (compte, connexion...).' ])
            ->execute();

        $container->query()->insertInto('menu_link', [ 'title_link', 'target_link',
                'menu', 'weight', 'parent', 'active' ])
            ->values([
                '<span class="glyphicon glyphicon-home" aria-hidden="true"></span> Accueil',
                '/',
                'admin-menu',
                1,
                -1,
                true
            ])
            ->values([
                '<span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span> Menu',
                'menu/main-menu',
                'admin-menu',
                3,
                -1,
                true
            ])
            ->values([
                'Accueil',
                '/',
                'main-menu',
                1,
                -1,
                true
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
            $container->query()->insertInto('permission', [ 'permission_id',
                    'permission_label' ])
                ->values([ 'menu.show', 'Voir les menus' ])
                ->values([ 'menu.show.check', 'Modifier le menu' ])
                ->values([ 'menu.link.add', 'Voir d\'ajout des liens de menu' ])
                ->values([ 'menu.link.add.check', 'Ajouter des liens de menu' ])
                ->values([ 'menu.link.edit', 'Voir l\'edition des liens de menu' ])
                ->values([ 'menu.link.edit.check', 'Editer des liens de menu' ])
                ->values([ 'menu.link.delete', 'Supprimer des liens de menu' ])
                ->execute();

            $container->query()->insertInto('role_permission', [ 'role_id',
                    'permission_id' ])
                ->values([ 3, 'menu.show' ])
                ->values([ 3, 'menu.show.check' ])
                ->values([ 3, 'menu.link.add' ])
                ->values([ 3, 'menu.link.add.check' ])
                ->values([ 3, 'menu.link.edit' ])
                ->values([ 3, 'menu.link.edit.check' ])
                ->values([ 3, 'menu.link.delete' ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->from('permission')
                ->delete()
                ->regex('permission_id', '/^menu./')
                ->execute();

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
