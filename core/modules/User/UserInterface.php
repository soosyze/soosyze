<?php

declare(strict_types=1);

namespace SoosyzeCore\User;

/**
 * @phpstan-type PermissionsEntity array<string, array<string, string>|array{ name: string, attr: array }>
 */
interface UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void;
}
