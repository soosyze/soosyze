<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Config\Hook;

use Core;

/**
 * @phpstan-import-type ConfigMenuEntity from \Soosyze\Core\Modules\Config\ConfigInterface
 */
class User implements \Soosyze\Core\Modules\User\UserInterface
{
    /**
     * @var Core
     */
    private $core;

    public function __construct(Core $core)
    {
        $this->core = $core;
    }

    public function hookUserPermissionModule(array &$permissions): void
    {
        /** @phpstan-var ConfigMenuEntity $menu */
        $menu = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);

        $permissions[ 'Configuration' ][ 'config.manage' ] = 'Administer all configurations';
        if ($menu === []) {
            return;
        }

        foreach ($menu as $key => $link) {
            $permissions[ 'Configuration' ][ $key . '.config.manage' ] = [
                'name' => 'Administer :name configurations',
                'attr' => [ ':name' => $link[ 'title_link' ] ]
            ];
        }
    }

    public function hookConfigAdmin(): array
    {
        /** @phpstan-var ConfigMenuEntity $menu */
        $menu = [];
        $this->core->callHook('config.edit.menu', [ &$menu ]);

        $out[] = 'config.manage';
        if ($menu === []) {
            return $out;
        }

        foreach (array_keys($menu) as $key) {
            $out[] = $key . '.config.manage';
        }

        return $out;
    }

    public function hookConfigManage(string $id): array
    {
        return [ 'config.manage', "$id.config.manage" ];
    }
}
