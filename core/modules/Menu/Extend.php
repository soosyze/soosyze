<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;

/**
 * @phpstan-type MenuEntity array{
 *      name: string,
 *      title: string,
 *      description: string
 * }
 * @phpstan-type MenuLinkEntity array{
 *      id: int,
 *      key: string|null,
 *      icon: string|null,
 *      link: string,
 *      link_router: string|null,
 *      query: string|null,
 *      fragment: string,
 *      title_link: string,
 *      target_link: bool,
 *      menu: string,
 *      weight: int,
 *      parent: int,
 *      has_children: bool,
 *      active: bool
 * }
 */
class Extend extends \SoosyzeCore\System\ExtendModule
{
    public function getDir(): string
    {
        return __DIR__;
    }

    public function boot(): void
    {
        foreach ([ 'block', 'main', 'permission' ] as $file) {
            $this->loadTranslation('fr', __DIR__ . "/Lang/fr/$file.json");
        }
    }

    public function install(ContainerInterface $ci): void
    {
        $ci->schema()
            ->createTableIfNotExists('menu', static function (TableBuilder $tb): void {
                $tb->string('name');
                $tb->string('title');
                $tb->text('description');
            })
            ->createTableIfNotExists('menu_link', static function (TableBuilder $tb): void {
                $tb->increments('id');
                $tb->string('key')->nullable();
                $tb->string('icon')->nullable();
                $tb->string('link');
                $tb->string('link_router')->nullable();
                $tb->string('query')->nullable();
                $tb->string('fragment')->nullable();
                $tb->string('title_link');
                $tb->boolean('target_link')->valueDefault(false);
                $tb->string('menu');
                $tb->integer('weight')->valueDefault(1);
                $tb->integer('parent');
                $tb->boolean('has_children')->valueDefault(false);
                $tb->boolean('active')->valueDefault(true);
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

    public function seeders(ContainerInterface $ci): void
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

    public function hookInstall(ContainerInterface $ci): void
    {
        if ($ci->module()->has('Block')) {
            $this->hookInstallBlock($ci);
        }
        if ($ci->module()->has('User')) {
            $this->hookInstallUser($ci);
        }
    }

    public function hookInstallBlock(ContainerInterface $ci): void
    {
        $ci->query()
            ->insertInto('block', [
                'title', 'is_title', 'section', 'hook',
                'weight', 'pages', 'key_block',
                'options',
                'theme'
            ])
            ->values([
                'Administration menu', false, 'main_menu', 'menu',
                0, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-admin', 'parent' => -1 ]),
                'admin'
            ])
            ->values([
                'User Menu', false, 'second_menu', 'menu',
                1, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-user', 'parent' => -1 ]),
                'admin'
            ])
            ->values([
                'Main Menu', false, 'main_menu', 'menu',
                0, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-main', 'parent' => -1 ]),
                'public'
            ])
            ->values([
                'User Menu', false, 'second_menu', 'menu',
                1, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-user', 'parent' => -1 ]),
                'public'
            ])
            ->execute();
    }

    public function hookInstallUser(ContainerInterface $ci): void
    {
        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'menu.administer' ])
            ->execute();
    }

    public function uninstall(ContainerInterface $ci): void
    {
        foreach ([ 'menu_link', 'menu' ] as $table) {
            $ci->schema()->dropTableIfExists($table);
        }
    }

    public function hookUninstall(ContainerInterface $ci): void
    {
        if ($ci->module()->has('Block')) {
            $this->hookUninstallBlock($ci);
        }
        if ($ci->module()->has('User')) {
            $this->hookUninstallUser($ci);
        }
    }

    public function hookUninstallBlock(ContainerInterface $ci): void
    {
        $ci->query()
            ->from('block')
            ->delete()
            ->where('hook', 'like', 'menu')
            ->execute();
    }

    public function hookUninstallUser(ContainerInterface $ci): void
    {
        $ci->query()
            ->from('role_permission')
            ->delete()
            ->where('permission_id', 'like', 'menu.%')
            ->execute();
    }
}
