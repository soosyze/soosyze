<?php

namespace SoosyzeCore\Template\Hook;

class User
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Template' ][ 'template.admin' ] = 'Use the admin theme';
    }

    public function hookInstallUser($ci)
    {
        $ci->query()
            ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'template.admin' ])
            ->execute();
    }
}
