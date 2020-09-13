<?php

namespace SoosyzeCore\FileManager\Services;

class HookRole
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function hookRoleDeleteBefore($validator, $id)
    {
        $this->query
            ->from('profil_file_role')
            ->where('role_id', '==', $id)
            ->delete()
            ->execute();
    }
}
