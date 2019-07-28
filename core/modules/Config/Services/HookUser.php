<?php

namespace SoosyzeCore\Config\Services;

class HookUser
{
    protected $core;

    public function __construct($core)
    {
        $this->core = $core;
    }

    public function hookPermission(&$permission)
    {
        $menu = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);
        $permission[ 'Config' ]['config.manage'] = 'Administrer toutes les configurations';
        foreach ($menu as $link) {
            $permission[ 'Config' ][$link[ 'key' ] . '.config.manage'] = 'Administrer les configurations ' . $link[ 'title_link' ];
        }
    }

    public function hookConfigIndex()
    {
        $menu = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);
        $out[] = 'config.manage';
        foreach ($menu as $link) {
            $out[] = $link[ 'key' ] . '.config.manage';
        }

        return $out;
    }

    public function hookConfigManage($id)
    {
        return ['config.manage', "$id.config.manage"];
    }
}
