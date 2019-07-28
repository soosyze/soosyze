<?php

namespace SoosyzeCore\User;

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
            ->createTableIfNotExists('user', function (TableBuilder $table) {
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
            })
            ->createTableIfNotExists('role', function (TableBuilder $table) {
                $table->increments('role_id')
                ->string('role_description')->nullable()
                ->string('role_label')
                ->string('role_color', 7)->valueDefault('#e6e7f4')
                ->integer('role_weight')->valueDefault(1);
            })
            ->createTableIfNotExists('user_role', function (TableBuilder $table) {
                $table->integer('user_id')
                ->integer('role_id');
            })
            ->createTableIfNotExists('role_permission', function (TableBuilder $table) {
                $table->integer('role_id')
                ->string('permission_id');
            });

        $ci->config()
            ->set('settings.user_register', '')
            ->set('settings.user_relogin', 'on')
            ->set('settings.password_show', 'on')
            ->set('settings.password_length', 8)
            ->set('settings.password_upper', 1)
            ->set('settings.password_digit', 1)
            ->set('settings.password_special', 1);
    }
    
    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('role', [ 'role_label', 'role_description', 'role_weight' ])
            ->values([ 'Utilisateur non connecté', 'Rôle requis par le système', 1 ])
            ->values([ 'Utilisateur connecté', 'Rôle requis par le système', 2 ])
            ->values([ 'Administrateur', 'Rôle requis par le système', 3 ])
            ->execute();

        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'user.permission.manage' ])
            ->values([ 3, 'user.people.manage' ])
            ->values([ 3, 'user.showed' ])
            ->values([ 3, 'user.edited' ])
            ->values([ 3, 'user.deleted' ])
            ->values([ 2, 'user.showed' ])
            ->values([ 2, 'user.edited' ])
            ->execute();
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallMenu($ci);
    }

    public function hookInstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->insertInto('menu_link', [
                    'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
                ])
                ->values([
                    'user.management.admin', 'fa fa-user', 'Utilisateur', 'admin/user', 'menu-admin', 4, -1
                ])
                ->values([
                    'user.account', null, 'Mon compte', 'user/account', 'menu-user', 1, -1
                ])
                ->values([
                    'user.login', null, 'Connexion', 'user/login', 'menu-user', 2, -1
                ])
                ->values([
                    'user.logout', 'fa fa-power-off', 'Déconnexion', 'user/logout', 'menu-user', 3, -1
                ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        // Table pivot
        $ci->schema()->dropTable('user_role');
        $ci->schema()->dropTable('role_permission');
        // Table référentes
        $ci->schema()->dropTable('user');
        $ci->schema()->dropTable('role');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallMenu($ci);
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->from('menu_link')
                ->delete()
                ->where('link', 'like', 'user%')
                ->orWhere('link', 'like', 'admin/user%')
                ->execute();
        }
    }
}
