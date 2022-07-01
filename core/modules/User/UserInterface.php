<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\User;

/**
 * @phpstan-type PermissionsEntity array<string, array<string, string>|array{ name: string, attr: array }>
 */
interface UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void;
}
