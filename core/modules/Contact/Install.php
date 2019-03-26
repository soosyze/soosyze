<?php

namespace Contact;

class Install
{
    public function install()
    {
    }

    public function hookInstall($container)
    {
        $this->hookInstallMenu($container);
        $this->hookInstallUser($container);
    }

    public function hookInstallMenu($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->insertInto('menu_link', [ 'key', 'title_link', 'link',
                    'menu', 'weight', 'parent' ])
                ->values([
                    'contact',
                    'Contact',
                    'contact',
                    'main-menu',
                    4,
                    -1
                ])
                ->execute();
        }
    }
    
    public function hookInstallUser($container)
    {
        if ($container->schema()->hasTable('user')) {
            $container->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'contact.main' ])
                ->values([ 2, 'contact.main' ])
                ->values([ 1, 'contact.main' ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->from('menu_link')
                ->delete()
                ->where('link', 'contact')
                ->execute();
        }
    }
}
