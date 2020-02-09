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
        /* Création du dossier de backup. */
        $dir = $ci->core()->getSetting('backup_dir', ROOT . '../soosyze_backups');
        if (!file_exists($dir)) {
            \mkdir($dir, 644, true);
        }
        
        $ci->config()
            ->set('settings.max_backups', 0)
            ->set('settings.backup_cron', 0);
    }

    public function seeders(ContainerInterface $ci)
    {
    }
    
    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallMenu($ci);
        $this->hookInstallUser($ci);
    }
    
    public function hookInstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'backups.manage' ])
                ->execute();
        }
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
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }
    
    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()->from('menu_link')
                ->delete()
                ->where('key', 'like', 'backupmanager.%')
                ->execute();
        }
    }
    
    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'backups.%')
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        /* Suppression du dossier de sauvegarde. */
        foreach (new \DirectoryIterator($ci->core()->getSetting('backup_dir')) as $file) {
            if ($file->isDot()) {
                continue;
            }
            \unlink($file->getPathname());
        }
        rmdir($ci->core()->getSetting('backup_dir', ROOT . '../soosyze_backups'));
    }
}
