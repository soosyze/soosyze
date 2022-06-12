<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Enum;

final class Menu
{
    public const ADMIN_MENU = 1;

    public const MAIN_MENU  = 2;

    public const USER_MENU  = 3;

    public const DEFAULT_MENU = [
        self::ADMIN_MENU,
        self::MAIN_MENU,
        self::USER_MENU
    ];
}
