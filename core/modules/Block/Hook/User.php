<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Hook;

class User implements \Soosyze\Core\Modules\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
        $permissions[ 'Block' ] = [
            'block.administer' => 'See the block administration page',
            'block.created'    => 'Create a new block',
            'block.edited'     => 'Edit blocks',
            'block.deleted'    => 'Delete blocks'
        ];
    }

    public function hookBlockAdmin(): string
    {
        return 'block.administer';
    }

    public function hookBlockCreated(): string
    {
        return 'block.created';
    }

    public function hookBlockEdited(): string
    {
        return 'block.edited';
    }

    public function hookBlockDeleted(): string
    {
        return 'block.deleted';
    }
}
