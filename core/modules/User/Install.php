<?php

namespace User;

use Queryflatfile\TableBuilder;

class Install
{
    public function install($container)
    {
        $container->schema()->createTableIfNotExists('user', function (TableBuilder $table) {
            $table->increments('user_id')
                ->string('email')
                ->string('username')
                ->text('password')
                ->text('salt')
                ->string('key_api')->nullable()
                ->string('color', 7)->valueDefault('#e6e7f4')
                ->string('picture')->nullable()
                ->string('bio')->nullable()
                ->string('firstname')->nullable()
                ->string('name')->nullable()
                ->boolean('actived')->valueDefault(false)
                ->text('token_forget')->nullable()
                ->text('token_actived')->nullable()
                ->string('time_reset')->nullable()
                ->string('time_installed')
                ->string('time_access')->nullable()
                ->text('timezone');
        });
        $container->schema()->createTableIfNotExists('role', function (TableBuilder $table) {
            $table->increments('role_id')
                ->string('role_description')->nullable()
                ->string('role_label')
                ->string('role_color', 7)->valueDefault('#e6e7f4')
                ->integer('role_weight')->valueDefault(1);
        });
        $container->schema()->createTableIfNotExists('user_role', function (TableBuilder $table) {
            $table->integer('user_id')
                ->integer('role_id');
        });
        $container->schema()->createTableIfNotExists('role_permission', function (TableBuilder $table) {
            $table->integer('role_id')
                ->string('permission_id');
        });

        $container->query()
            ->insertInto('role', [ 'role_label', 'role_description', 'role_weight' ])
            ->values([ 'Utilisateur non connecté', 'Rôle requis par le système', 1 ])
            ->values([ 'Utilisateur connecté', 'Rôle requis par le système', 2 ])
            ->values([ 'Administrateur', 'Rôle requis par le système', 3 ])
            ->execute();

        $container->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'user.config.manage' ])
            ->values([ 3, 'user.permission.manage' ])
            ->values([ 3, 'user.people.manage' ])
            ->values([ 3, 'user.showed' ])
            ->values([ 3, 'user.edited' ])
            ->values([ 3, 'user.deleted' ])
            ->values([ 2, 'user.showed' ])
            ->values([ 2, 'user.edited' ])
            ->execute();

        $container->config()->set('settings.user_register', '');
        $container->config()->set('settings.user_relogin', '');
    }

    public function hookInstall($container)
    {
        $this->hookInstallMenu($container);
    }

    public function hookInstallMenu($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->insertInto('menu_link', [ 'key', 'title_link', 'link',
                    'menu', 'weight', 'parent' ])
                ->values([
                    'user.management.admin',
                    '<i class="fa fa-user"></i> Utilisateur',
                    'admin/user',
                    'admin-menu',
                    4,
                    -1
                ])
                ->values([
                    'user.account',
                    'Mon compte',
                    'user/account',
                    'user-menu',
                    1,
                    -1
                ])
                ->values([
                    'user.login',
                    'Connexion',
                    'user/login',
                    'user-menu',
                    2,
                    -1
                ])
                ->values([
                    'user.logout',
                    '<i class="fa fa-power-off"></i> Déconnexion',
                    'user/logout',
                    'user-menu',
                    3,
                    -1
                ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()
                ->from('menu_link')
                ->delete()
                ->regex('link', '/^user/')
                ->execute();
        }
        // Table pivot
        $container->schema()->dropTable('user_role');
        $container->schema()->dropTable('role_permission');
        // Table référentes
        $container->schema()->dropTable('user');
        $container->schema()->dropTable('role');
    }
}
