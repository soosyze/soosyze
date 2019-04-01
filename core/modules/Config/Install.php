<?php

namespace Config;

class Install
{
    public function install($container)
    {
    }

    public function hookInstall($container)
    {
        $this->hookInstallMenu($container);
    }

    public function hookInstallUser($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'config.manage' ])
                ->execute();
        }
    }

    public function hookInstallMenu($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->insertInto('menu_link', [
                    'key',
                    'title_link',
                    'link',
                    'menu',
                    'weight',
                    'parent'
                ])
                ->values([
                    'config.index',
                    '<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Configuration',
                    'admin/config',
                    'admin-menu',
                    6,
                    -1
                ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->from('role_permission')
                ->delete()
                ->regex('permission_id', '/^config./')
                ->execute();
        }

        if ($container->schema()->hasTable('menu')) {
            $container->query()
                ->from('menu_link')
                ->delete()
                ->where('link', 'admin/config')
                ->execute();
        }
    }
}
