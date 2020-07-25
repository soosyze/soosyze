<?php

namespace SoosyzeCore\BackupManager;

use Psr\Container\ContainerInterface;

class Installer extends \SoosyzeCore\System\Migration
{
    public function getDir()
    {
        return __DIR__;
    }
    
    public function boot()
    {
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/main.json');
    }
    
    public function install(ContainerInterface $ci)
    {
        /* Création du dossier de backup. */
        $dir = $ci->core()->getDir('backup_dir', '../soosyze_backups/default');
        if (!is_dir($dir)) {
            \mkdir($dir, 0755, true);
            $f = fopen($dir . '/.htaccess', 'w');
            fwrite($f, '
# Apache 2.4+.
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>

# Apache 2.0 & 2.2.*
<IfModule !mod_authz_core.c>
  Deny from all
</IfModule>

Options None
Options +FollowSymLinks

<IfModule mod_php5.c>
  php_flag engine off
</IfModule>');
            fclose($f);
        }

        $ci->config()
            ->set('settings.max_backups', 30)
            ->set('settings.backup_frequency', '1 day')
            ->set('settings.backup_time', 0)
            ->set('settings.backup_cron', false);
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
                    'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent',
                    'active'
                ])
                ->values([ 'backupmanager.index', 'fas fa-file-archive', 'Backups',
                    'admin/backupmanager/backups', 'menu-admin', 50, -1, true ])
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
            $ci->menu()->deleteLinks(function () use ($ci) {
                return $ci->query()
                        ->from('menu_link')
                        ->where('key', 'like', 'backupmanager%')
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
                ->where('permission_id', 'like', 'backups.%')
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $dir = $ci->core()->getDir('backup_dir', '../soosyze_backups');
        if (!is_dir($dir)) {
            return;
        }
        /* Suppression du dossier de sauvegarde. */
        foreach (new \DirectoryIterator($dir) as $file) {
            if ($file->isDot()) {
                continue;
            }
            \unlink($file->getPathname());
        }
        rmdir($dir);
    }
}
