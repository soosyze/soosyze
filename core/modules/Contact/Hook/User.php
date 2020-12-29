<?php

namespace SoosyzeCore\Contact\Hook;

class User
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Contact' ] = [
            'contact.main' => 'Use the general contact form'
        ];
    }

    public function hookContact()
    {
        return 'contact.main';
    }
}
