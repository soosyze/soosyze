<?php

namespace SoosyzeCore\BackupManager;

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
        $ci->config()
            ->set('settings.max_backups', 0);
    }

    public function seeders(ContainerInterface $ci)
    {
    }
    
    public function hookInstall(ContainerInterface $ci)
    {
        $dir = $ci->core()->getSetting('backup_dir');
        if (!file_exists($dir)) {
            \mkdir($dir, 644, true);
        }
        $this->hookInstallMenu($ci);
    }

    public function hookInstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->insertInto('menu_link', [
                    'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent', 'active'
                ])
                ->values([ 'backupmanager.index', 'fas fa-file-archive', 'Backups', 'admin/backupmanager/backups', 'menu-admin', 50, -1, true ])
                ->execute();
        }
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()->from('menu_link')
                ->delete()
                ->where('key', 'like', 'backupmanager.%')
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
    }
}
