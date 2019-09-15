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
        foreach ($menu as $key => $link) {
            $permission[ 'Configuration' ][$key . '.config.manage'] = t('Administer :name configurations', [':name' => $link[ 'title_link' ]]);
        }
    }

    public function hookConfigIndex()
    {
        $menu = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);
        $out[] = 'config.manage';
        foreach (array_keys($menu) as $key) {
            $out[] = $key . '.config.manage';
        }

        return $out;
    }

    public function hookConfigManage($id)
    {
        return ['config.manage', "$id.config.manage"];
    }
}
