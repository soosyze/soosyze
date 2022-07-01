<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Contact\Hook;

class User implements \Soosyze\Core\Modules\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
        $permissions[ 'Contact' ] = [
            'contact.main' => 'Use the general contact form'
        ];
    }

    public function hookContact(): string
    {
        return 'contact.main';
    }
}
