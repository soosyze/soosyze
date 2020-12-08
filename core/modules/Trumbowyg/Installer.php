<?php

namespace SoosyzeCore\Trumbowyg;

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
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/permission.json');
    }

    public function install(ContainerInterface $ci)
    {
    }

    public function uninstall(ContainerInterface $ci)
    {
    }

    public function hookInstall(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $this->hookInstallUser($ci);
        }
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 2, 'trumbowyg.upload' ])
            ->values([ 3, 'trumbowyg.upload' ])
            ->execute();
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $this->hookUninstallUser($ci);
        }
    }

    public function hookUninstallUser(ContainerInterface $ci)
    {
        $ci->query()
            ->from('role_permission')
            ->delete()
            ->where('permission_id', 'like', 'trumbowyg%')
            ->execute();
    }

    public function seeders(ContainerInterface $ci)
    {
    }
}
