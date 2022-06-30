<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Hook;

use Psr\Http\Message\ServerRequestInterface;
use SoosyzeCore\Menu\Enum\Menu;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
        $permissions[ 'Menu' ] = [
            'menu.administer' => 'Administer menus and menu items'
        ];
    }

    public function hookMenuAdminister(): string
    {
        return 'menu.administer';
    }

    public function hookMenuDelete(int $menuId): ?string
    {
        return in_array($menuId, Menu::DEFAULT_MENU)
            ? null
            : 'menu.administer';
    }

    public function hookMenuApiShow(
        int $menuId,
        ?ServerRequestInterface $req,
        ?array $user
    ): bool {
        return !empty($user);
    }
}
