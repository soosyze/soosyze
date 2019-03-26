<?php

namespace Contact\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Contact' ] = [
            'contact.main' => 'Utiliser le formulaire de contact général'
        ];
    }

    public function hookRouteContact()
    {
        return 'contact.main';
    }
}
