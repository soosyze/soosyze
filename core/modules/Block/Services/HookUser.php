<?php

namespace SoosyzeCore\Block\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Block' ] = [
            'block.administer' => 'Accéder à la page d\'administration des blocs',
            'block.created'    => 'Créer un nouveau bloc',
            'block.edited'     => 'Mofifier les blocs',
            'block.deleted'    => 'Supprimer un bloc'
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
