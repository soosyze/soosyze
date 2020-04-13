<?php

namespace SoosyzeCore\FileManager;

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
            ->createTableIfNotExists('profil_file', function (TableBuilder $table) {
                $table
                ->increments('profil_file_id')
                ->text('folder_show')
                ->boolean('folder_show_sub')->valueDefault(true)
                ->integer('profil_weight')->valueDefault(1)
                ->boolean('folder_store')->valueDefault(true)
                ->boolean('folder_update')->valueDefault(false)
                ->boolean('folder_delete')->valueDefault(true)
                ->integer('folder_size')->valueDefault(10)
                ->boolean('file_store')->valueDefault(true)
                ->boolean('file_update')->valueDefault(false)
                ->boolean('file_delete')->valueDefault(false)
                ->boolean('file_download')->valueDefault(true)
                ->boolean('file_clipboard')->valueDefault(true)
                ->integer('file_size')->valueDefault(1)
                ->boolean('file_extensions_all')->valueDefault(false)
                ->text('file_extensions')->valueDefault('');
            })
            ->createTableIfNotExists('profil_file_role', function (TableBuilder $table) {
                $table
                ->integer('profil_file_id')
                ->integer('role_id');
            });
        $ci->config()->set('settings.replace_file', 1);
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('profil_file', [
                'folder_show',
                'folder_show_sub',
                'profil_weight',
                'folder_store',
                'folder_update',
                'folder_delete',
                'folder_size',
                'file_store',
                'file_update',
                'file_delete',
                'file_download',
                'file_clipboard',
                'file_size',
                'file_extensions_all',
                'file_extensions'
            ])
            ->values([
                '/',
                true,
                1,
                true,
                true,
                true,
                100,
                true,
                true,
                true,
                true,
                true,
                1,
                true,
                ''
            ])
            ->values([
                '/user/:user_id',
                true,
                2,
                false,
                false,
                false,
                10,
                true,
                true,
                true,
                true,
                true,
                1,
                false,
                'jpg,jpeg,gif,png,pdf'
            ])
            ->execute();

        $ci->query()
            ->insertInto('profil_file_role', [ 'profil_file_id', 'role_id' ])
            ->values([ 1, 3 ])
            ->values([ 2, 2 ])
            ->execute();
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
                ->values([ 3, 'filemanager.profil.admin' ])
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
                    'filemanager.admin', 'fa fa-folder', 'File', 'admin/filemanager/show',
                    'menu-admin', 50, -1
                ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->schema()->dropTable('profil_file');
        $ci->schema()->dropTable('profil_file_role');
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
                ->where('key', 'like', 'filemanager.%')
                ->execute();
        }
    }

    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'filemanager.%')
                ->execute();
        }
    }
}
