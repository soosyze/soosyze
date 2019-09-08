<?php

namespace SoosyzeCore\Node;

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
            ->createTableIfNotExists('node', function (TableBuilder $table) {
                $table->increments('id')
                ->string('title')
                ->string('type')
                ->string('created')
                ->string('changed')
                ->boolean('published')
                ->text('field');
            })
            ->createTableIfNotExists('node_type', function (TableBuilder $table) {
                $table->string('node_type')
                ->string('node_type_name')
                ->text('node_type_description');
            })
            ->createTableIfNotExists('field', function (TableBuilder $table) {
                $table->increments('field_id')
                ->string('field_name')
                ->string('field_type')
                ->string('field_rules');
            })
            ->createTableIfNotExists('node_type_field', function (TableBuilder $table) {
                $table->string('node_type')
                ->integer('field_id')
                ->string('field_label')
                ->text('field_description')->valueDefault('')
                ->text('field_default_value')->nullable()
                ->integer('field_weight')->valueDefault(1);
            });

        $ci->query()->insertInto('node_type', [
                'node_type', 'node_type_name', 'node_type_description'
            ])
            ->values([ 'page', 'Page', 'Utilisez les pages pour votre contenu statique.' ])
            ->execute();

        $ci->query()->insertInto('field', [
                'field_name', 'field_type', 'field_rules'
            ])
            ->values([ 'body', 'textarea', 'required|string' ])
            ->values([ 'summary', 'textarea', '!required|string|max:255' ])
            ->execute();

        $ci->query()
            ->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_label'
            ])
            ->values([ 'page', 1, 'Corps' ])
            ->execute();
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
                ->values([ 3, 'node.show.not_published' ])
                ->values([ 3, 'node.show.published' ])
                ->values([ 3, 'node.administer' ])
                ->values([ 3, 'node.index' ])
                ->values([ 2, 'node.show.published' ])
                ->values([ 1, 'node.show.published' ])
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
                    'node.index', 'fa fa-file', 'Contenu', 'admin/node', 'menu-admin',
                    2, -1
                ])
                ->values([
                    'node.show', null, 'Accueil', '/', 'menu-main', 1, -1
                ])
                ->values([
                    'node.show', 'fa fa-home', 'Accueil', '/', 'menu-admin', 1, -1
                ])
                ->execute();

            $ci->schema()
                ->createTableIfNotExists('node_menu_link', function (TableBuilder $table) {
                    $table->integer('node_id')
                    ->integer('menu_link_id');
                });
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->schema()->dropTable('node_type_field');
        $ci->schema()->dropTable('field');
        $ci->schema()->dropTable('node_type');
        $ci->schema()->dropTable('node');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->schema()->hasTable('node_menu_link')) {
            $ci->schema()->dropTable('node_menu_link');
        }
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->from('menu_link')
                ->delete()
                ->orWhere('key', 'like', 'node%')
                ->execute();
        }
    }

    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'node%')
                ->execute();
        }
    }
}
