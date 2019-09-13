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
        $permission[ 'Configuration' ]['config.manage'] = t('Administer all configurations');
        foreach ($menu as $link) {
            $permission[ 'Configuration' ][$link[ 'key' ] . '.config.manage'] = t('Administer :name configurations', [':name' => $link[ 'title_link' ]]);
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
