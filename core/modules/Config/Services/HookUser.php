<?php

namespace Config\Services;

class HookUser
{
    protected $core;

    public function __construct($core)
    {
        $this->core = $core;
    }

    public function hookConfigIndex()
    {
        $menu = $out = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);
        foreach ($menu as $link) {
            $out[] = $link[ 'key' ] . '.config.manage';
        }

        return $out;
    }

    public function hookConfigManage($id)
    {
        return "$id.config.manage";
    }
}
