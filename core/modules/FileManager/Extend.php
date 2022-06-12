<?php

declare(strict_types=1);

namespace SoosyzeCore\FileManager;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;
use SoosyzeCore\FileManager\Hook\Config;
use SoosyzeCore\Menu\Enum\Menu;

/**
 * @phpstan-type ProfilFileEntity array{
 *      profil_file_id: int,
 *      folder_show: string,
 *      folder_show_sub: bool,
 *      profil_weight: int,
 *      folder_store: bool,
 *      folder_update: bool,
 *      folder_delete: bool,
 *      folder_download: bool,
 *      folder_size: int,
 *      file_store: bool,
 *      file_update: bool,
 *      file_delete: bool,
 *      file_download: bool,
 *      file_clipboard: bool,
 *      file_copy: bool,
 *      file_size: int,
 *      file_extensions_all: bool,
 *      file_extensions: string
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
        foreach ([ 'config', 'main', 'permission' ] as $file) {
            $this->loadTranslation('fr', __DIR__ . "/Lang/fr/$file.json");
        }
    }

    public function install(ContainerInterface $ci): void
    {
        $ci->schema()
            ->createTableIfNotExists('profil_file', static function (TableBuilder $tb): void {
                $tb->increments('profil_file_id');
                $tb->text('folder_show');
                $tb->boolean('folder_show_sub')->valueDefault(true);
                $tb->integer('profil_weight')->valueDefault(1);
                $tb->boolean('folder_store')->valueDefault(true);
                $tb->boolean('folder_update')->valueDefault(false);
                $tb->boolean('folder_delete')->valueDefault(true);
                $tb->boolean('folder_download')->valueDefault(false);
                $tb->integer('folder_size')->valueDefault(10);
                $tb->boolean('file_store')->valueDefault(true);
                $tb->boolean('file_update')->valueDefault(false);
                $tb->boolean('file_delete')->valueDefault(false);
                $tb->boolean('file_download')->valueDefault(true);
                $tb->boolean('file_clipboard')->valueDefault(true);
                $tb->boolean('file_copy')->valueDefault(true);
                $tb->integer('file_size')->valueDefault(1);
                $tb->boolean('file_extensions_all')->valueDefault(false);
                $tb->text('file_extensions')->valueDefault('');
            })
            ->createTableIfNotExists('profil_file_role', static function (TableBuilder $tb): void {
                $tb->integer('profil_file_id');
                $tb->integer('role_id');
            });
        $ci->config()->set('settings.replace_file', Config::REPLACE_WITH);
        $ci->config()->set('settings.copy_link_file', Config::COPY_ABSOLUTE);

        $dir = $ci->core()->getDir('files_public', 'app/files');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function seeders(ContainerInterface $ci): void
    {
        $ci->query()
            ->insertInto('profil_file', [
                'profil_file_id',
                'folder_show',
                'folder_show_sub',
                'profil_weight',
                'folder_store',
                'folder_update',
                'folder_delete',
                'folder_download',
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
                1,
                '/user/:user_id',
                true,
                1,
                true,
                true,
                true,
                true,
                20,
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
                2,
                '/node',
                true,
                2,
                false,
                false,
                false,
                false,
                10,
                false,
                false,
                false,
                false,
                false,
                1,
                true,
                ''
            ])
            ->values([
                3,
                '/node/',
                true,
                3,
                true,
                true,
                true,
                true,
                10,
                true,
                true,
                false,
                true,
                true,
                0,
                true,
                ''
            ])
            ->values([
                4,
                '/dowload',
                true,
                4,
                false,
                false,
                false,
                false,
                10,
                false,
                false,
                false,
                true,
                false,
                1,
                true,
                ''
            ])
            ->values([
                5,
                '/',
                true,
                5,
                true,
                true,
                true,
                true,
                0,
                true,
                true,
                true,
                true,
                true,
                0,
                true,
                ''
            ])
            ->execute();

        $ci->query()
            ->insertInto('profil_file_role', [ 'profil_file_id', 'role_id' ])
            ->values([ 1, 2 ])
            ->values([ 2, 3 ])
            ->values([ 3, 2 ])
            ->values([ 4, 1 ])
            ->values([ 5, 3 ])
            ->execute();
    }

    public function hookInstall(ContainerInterface $ci): void
    {
        if ($ci->module()->has('User')) {
            $this->hookInstallUser($ci);
        }
        if ($ci->module()->has('Menu')) {
            $this->hookInstallMenu($ci);
        }
    }

    public function hookInstallUser(ContainerInterface $ci): void
    {
        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'filemanager.permission.admin' ])
            ->execute();
    }

    public function hookInstallMenu(ContainerInterface $ci): void
    {
        $ci->query()
            ->insertInto('menu_link', [
                'key', 'icon', 'title_link', 'link', 'menu_id', 'weight', 'parent'
            ])
            ->values([
                'filemanager.admin', 'fa fa-folder', 'File', 'admin/filemanager/show',
                Menu::ADMIN_MENU, 50, -1
            ])
            ->execute();
    }

    public function uninstall(ContainerInterface $ci): void
    {
        foreach ([ 'profil_file_role', 'profil_file' ] as $table) {
            $ci->schema()->dropTableIfExists($table);
        }
    }

    public function hookUninstall(ContainerInterface $ci): void
    {
        if ($ci->module()->has('Menu')) {
            $this->hookUninstallMenu($ci);
        }
        if ($ci->module()->has('User')) {
            $this->hookUninstallUser($ci);
        }
    }

    public function hookUninstallMenu(ContainerInterface $ci): void
    {
        $ci->menu()->deleteLinks(static function () use ($ci): array {
            return $ci->query()
                ->from('menu_link')
                ->where('key', 'like', 'filemanager%')
                ->fetchAll();
        });
    }

    public function hookUninstallUser(ContainerInterface $ci): void
    {
        $ci->query()
            ->from('role_permission')
            ->delete()
            ->where('permission_id', 'like', 'filemanager.%')
            ->execute();
    }
}
