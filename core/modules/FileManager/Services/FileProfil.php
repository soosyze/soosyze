<?php

namespace SoosyzeCore\FileManager\Services;

class FileProfil
{
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function find($profilId)
    {
        return $this->query->from('profil_file')
                ->where('profil_file_id', (int) $profilId)
                ->fetch();
    }

    public function getProfil($userId)
    {
        return $this->query->from('user_role')
                ->leftJoin('role', 'role_id', 'role.role_id')
                ->leftJoin('profil_file_role', 'role_id', 'role.role_id')
                ->rightJoin('profil_file', 'profil_file_id', 'profil_file.profil_file_id')
                ->where('user_id', '==', $userId)
                ->orderBy('role_weight', 'desc')
                ->orderBy('profil_weight', 'desc')
                ->fetchAll();
    }

    public function getRolesUserByProfil($profilId)
    {
        return $this->query->from('profil_file_role')
                ->leftJoin('role', 'role_id', 'role.role_id')
                ->where('profil_file_id', '==', $profilId)
                ->fetchAll();
    }

    public function getIdRolesUser($profilId)
    {
        $data = $this->getRolesUserByProfil($profilId);
        $out  = [];
        foreach ($data as $value) {
            $out[] = $value[ 'role_id' ];
        }

        return $out;
    }

    public function getProfilsFileByUser($userId)
    {
        if (!empty($this->profil[ $userId ])) {
            return $this->profil[ $userId ];
        }
        $this->profil[ $userId ] = $this->getProfil($userId);

        return $this->profil[ $userId ];
    }
}
