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
    }

    public function hookInstallMenu($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->insertInto('menu_link', [ 'title_link', 'link',
                    'menu', 'weight', 'parent'])
                ->values([
                    'Contact',
                    'contact',
                    'main-menu',
                    4,
                    -1
                ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->from('menu_link')
                ->delete()
                ->where('target_link', 'contact')
                ->execute();
        }
    }
}
