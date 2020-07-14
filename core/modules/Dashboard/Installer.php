<?php

namespace SoosyzeCore\Dashboard;

use Psr\Container\ContainerInterface;

class Installer extends \SoosyzeCore\System\Migration
{
    public function getDir()
    {
        return __DIR__;
    }
    
    public function boot()
    {
        $this->loadTranslation('fr', __DIR__ . '/lang/fr/main.json');
    }
    
    public function install(ContainerInterface $ci)
    {
    }

    public function seeders(ContainerInterface $ci)
    {
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallMenu($ci);
        $this->hookInstallUser($ci);
    }

    public function hookInstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->insertInto('menu_link', [
                    'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
                ])
                ->values([
                    'dashboard.index', 'fas fa-tachometer-alt', 'Dashboard', 'admin/dashboard',
                    'menu-admin', 1, -1
                ])
                ->execute();
        }
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'dashboard.administer' ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->menu()->deleteLinks(function () use ($ci) {
                return $ci->query()
                        ->from('menu_link')
                        ->where('key', 'like', 'dashboard%')
                        ->fetchAll();
            });
        }
    }

    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'dashboard.%')
                ->execute();
        }
    }
}
