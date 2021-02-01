<?php

namespace SoosyzeCore\Template\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'Template' ][ 'template.admin' ] = 'Use the admin theme';
    }

    public function hookInstallUser($ci)
    {
        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'template.admin' ])
            ->execute();
    }
}
