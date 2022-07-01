<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Template\Hook;

use Psr\Container\ContainerInterface;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;

class User implements \Soosyze\Core\Modules\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
        $permissions[ 'Template' ][ 'template.admin' ] = 'Use the admin theme';
    }

    public function hookInstallUser(ContainerInterface $ci): void
    {
        /** @phpstan-var Query $query */
        $query = $ci->get(Query::class);
        $query->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'template.admin' ])
            ->execute();
    }
}
