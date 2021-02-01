<?php

namespace SoosyzeCore\Block\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'Block' ] = [
            'block.administer' => 'See the block administration page',
            'block.created'    => 'Create a new block',
            'block.edited'     => 'Edit blocks',
            'block.deleted'    => 'Delete blocks'
        ];
    }

    public function hookBlockAdmin()
    {
        return 'block.administer';
    }

    public function hookBlockCreated()
    {
        return 'block.created';
    }

    public function hookBlockEdited()
    {
        return 'block.edited';
    }

    public function hookBlockDeleted()
    {
        return 'block.deleted';
    }
}
