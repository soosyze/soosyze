<?php

namespace SoosyzeCore\Contact\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Contact' ] = [
            'contact.main' => t('Use the general contact form')
        ];
    }

    public function hookContact()
    {
        return 'contact.main';
    }
}
