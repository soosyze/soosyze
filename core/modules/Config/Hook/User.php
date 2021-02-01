<?php

namespace SoosyzeCore\Config\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    /**
     * @var \Soosyze\App
     */
    private $core;

    public function __construct($core)
    {
        $this->core = $core;
    }

    public function hookUserPermissionModule(array &$permissions)
    {
        $menu = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);

        $permissions[ 'Configuration' ][ 'config.manage' ] = 'Administer all configurations';
        foreach ($menu as $key => $link) {
            $permissions[ 'Configuration' ][ $key . '.config.manage' ] = [
                'name' => 'Administer :name configurations',
                'attr'  => [ ':name' => $link[ 'title_link' ] ]
            ];
        }
    }

    public function hookConfigAdmin()
    {
        $menu  = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);

        $out[] = 'config.manage';
        foreach (array_keys($menu) as $key) {
            $out[] = $key . '.config.manage';
        }

        return $out;
    }

    public function hookConfigManage($id)
    {
        return [ 'config.manage', "$id.config.manage" ];
    }
}
