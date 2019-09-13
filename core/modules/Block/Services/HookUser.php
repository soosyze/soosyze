<?php

namespace SoosyzeCore\Block\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Block' ] = [
            'block.administer' => t('See the block administration page'),
            'block.created'    => t('Create a new block'),
            'block.edited'     => t('Edit blocks'),
            'block.deleted'    => t('Delete a block')
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
