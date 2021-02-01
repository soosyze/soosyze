<?php

namespace SoosyzeCore\Contact\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'Contact' ] = [
            'contact.main' => 'Use the general contact form'
        ];
    }

    public function hookContact()
    {
        return 'contact.main';
    }
}
