<?php

declare(strict_types=1);

namespace SoosyzeCore\User;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;
use SoosyzeCore\Menu\Enum\Menu;
use SoosyzeCore\User\Hook\Config;

/**
 * @phpstan-type UserEntity array{
 *      user_id: int,
 *      email: string,
 *      username: string,
 *      password: string,
 *      key_api: string|null,
 *      color: string,
 *      picture: string|null,
 *      bio: string|null,
 *      firstname: string|null,
 *      name: string|null,
 *      actived: bool,
 *      token_connected: string|null,
 *      token_forget: string|null,
 *      token_actived: string|null,
 *      time_reset: string|null,
 *      time_installed: string,
 *      time_access: string|null,
 *      rgpd: bool,
 *      terms_of_service: bool,
 *      timezone: string
 * }
 * @phpstan-type RoleEntity array{
 *      role_id: int,
 *      role_description: string|null,
 *      role_label: string,
 *      role_color: string|null,
 *      role_icon: string,
 *      role_weight: int,
 * }
 * @phpstan-type UserRoleEntity array{
 *      user_id: int,
 *      role_id: int
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
        foreach ([ 'block', 'config', 'main', 'permission' ] as $file) {
            $this->loadTranslation('fr', __DIR__ . "/Lang/fr/$file.json");
        }
    }

    public function install(ContainerInterface $ci): void
    {
        $ci->schema()
            ->createTableIfNotExists('user', static function (TableBuilder $tb): void {
                $tb->increments('user_id');
                $tb->string('email');
                $tb->string('username');
                $tb->text('password');
                $tb->string('key_api')->nullable();
                $tb->string('color', 7)->valueDefault('#e6e7f4');
                $tb->string('picture')->nullable();
                $tb->string('bio')->nullable();
                $tb->string('firstname')->nullable();
                $tb->string('name')->nullable();
                $tb->boolean('actived')->valueDefault(false);
                $tb->text('token_connected')->nullable();
                $tb->text('token_forget')->nullable();
                $tb->text('token_actived')->nullable();
                $tb->string('time_reset')->nullable();
                $tb->string('time_installed');
                $tb->string('time_access')->nullable();
                $tb->boolean('rgpd')->valueDefault(false);
                $tb->boolean('terms_of_service')->valueDefault(false);
                $tb->text('timezone');
            })
            ->createTableIfNotExists('role', static function (TableBuilder $tb): void {
                $tb->increments('role_id');
                $tb->string('role_description')->nullable();
                $tb->string('role_label');
                $tb->string('role_color', 7)->valueDefault('#e6e7f4');
                $tb->string('role_icon')->nullable();
                $tb->integer('role_weight')->valueDefault(1);
            })
            ->createTableIfNotExists('user_role', static function (TableBuilder $tb): void {
                $tb->integer('user_id');
                $tb->integer('role_id');
            })
            ->createTableIfNotExists('role_permission', static function (TableBuilder $tb): void {
                $tb->integer('role_id');
                $tb->string('permission_id');
            });

        $ci->query()
            ->insertInto('role', [
                'role_label', 'role_description', 'role_weight', 'role_icon', 'role_color'
            ])
            ->values([ 'User not logged in', 'Role required by the system', 1, 'fas fa-paper-plane', '#e5941f' ])
            ->values([ 'User logged in', 'Role required by the system', 2, 'fas fa-bolt', '#fe4341' ])
            ->values([ 'Administrator', 'Role required by the system', 3, 'fas fa-crown', '#858eec' ])
            ->execute();

        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'role.all' ])
            ->values([ 3, 'user.permission.manage' ])
            ->values([ 3, 'user.people.manage' ])
            ->values([ 3, 'user.showed' ])
            ->values([ 3, 'user.edited' ])
            ->values([ 3, 'user.deleted' ])
            ->values([ 2, 'user.showed' ])
            ->values([ 2, 'user.edited' ])
            ->execute();

        $ci->config()
            ->set('settings.user_delete', Config::DELETE_ACCOUNT_AND_ASSIGN)
            ->set('settings.user_register', Config::USER_REGISTER)
            ->set('settings.user_relogin', Config::USER_RELOGIN)
            ->set('settings.terms_of_service_show', Config::TERMS_OF_SERVICE_SHOW)
            ->set('settings.terms_of_service_page', Config::TERMS_OF_SERVICE_PAGE)
            ->set('settings.rgpd_show', Config::RGPD_SHOW)
            ->set('settings.rgpd_page', Config::RGPD_PAGE)
            ->set('settings.connect_url', Config::CONNECT_URL)
            ->set('settings.connect_redirect', Config::CONNECT_REDIRECT)
            ->set('settings.connect_https', true)
            ->set('settings.password_show', Config::PASSWORD_SHOW)
            ->set('settings.password_policy', Config::PASSWORD_POLICY)
            ->set('settings.password_length', Config::PASSWORD_LENGTH)
            ->set('settings.password_upper', Config::PASSWORD_UPPER)
            ->set('settings.password_digit', Config::PASSWORD_DIGIT)
            ->set('settings.password_special', Config::PASSWORD_SPECIAL)
            ->set('settings.password_reset_timeout', Config::PASSWORD_RESET_TIMEOUT);
    }

    public function seeders(ContainerInterface $ci): void
    {
    }

    public function hookInstall(ContainerInterface $ci): void
    {
        if ($ci->module()->has('Menu')) {
            $this->hookInstallMenu($ci);
        }
    }

    public function hookInstallMenu(ContainerInterface $ci): void
    {
        $ci->query()
            ->insertInto('menu_link', [
                'key', 'icon', 'title_link', 'link', 'menu_id', 'weight', 'parent'
            ])
            ->values([
                'user.admin', 'fa fa-user', 'User', 'admin/user',
                Menu::ADMIN_MENU, 4, -1
            ])
            ->values([
                'user.account', null, 'My account', 'user/account', Menu::USER_MENU,
                1, -1
            ])
            ->values([
                'user.login', null, 'Sign in', 'user/login', Menu::USER_MENU, 2,
                -1
            ])
            ->values([
                'user.logout', 'fa fa-power-off', 'Sign out', 'user/logout',
                Menu::USER_MENU, 3, -1
            ])
            ->values([
                'user.register.create', 'fas fa-user-circle', 'Sign up', 'user/register',
                Menu::USER_MENU, 4, -1
            ])
            ->execute();
    }

    public function uninstall(ContainerInterface $ci): void
    {
        foreach ([ 'user_role', 'role_permission', 'user', 'role' ] as $table) {
            $ci->schema()->dropTableIfExists($table);
        }
    }

    public function hookUninstall(ContainerInterface $ci): void
    {
        if ($ci->module()->has('Menu')) {
            $this->hookUninstallMenu($ci);
        }
    }

    public function hookUninstallBlock(ContainerInterface $ci): void
    {
        $ci->query()
            ->from('block')
            ->delete()
            ->where('hook', 'like', 'user.%')
            ->execute();
    }

    public function hookUninstallMenu(ContainerInterface $ci): void
    {
        $ci->menu()->deleteLinks(static function () use ($ci): array {
            return $ci->query()
                ->from('menu_link')
                ->where('key', 'like', 'user%')
                ->fetchAll();
        });
    }
}
