<?php

namespace SoosyzeCore\Contact\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Contact' ] = [
            'contact.main' => 'Utiliser le formulaire de contact général'
        ];
    }

    public function hookContact()
    {
        return 'contact.main';
    }
}
