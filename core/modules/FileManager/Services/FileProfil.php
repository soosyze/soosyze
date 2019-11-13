<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SoosyzeCore\FileManager\Services;

/**
 * Description of FileProfil
 *
 * @author mnoel
 */
class FileProfil
{
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function find($profil_id)
    {
        return $this->query->from('profil_file')
                ->where('profil_file_id', (int) $profil_id)
                ->fetch();
    }

    public function getProfil($user_id)
    {
        return $this->query->from('user_role')
                ->leftJoin('role', 'role_id', 'role.role_id')
                ->leftJoin('profil_file_role', 'role_id', 'role.role_id')
                ->rightJoin('profil_file', 'profil_file_id', 'profil_file.profil_file_id')
                ->where('user_id', '==', $user_id)
                ->orderBy('role_weight', 'desc')
                ->orderBy('profil_weight', 'desc')
                ->fetchAll();
    }

    public function getRolesUserByProfil($profil_id)
    {
        return $this->query->from('profil_file_role')
                ->leftJoin('role', 'role_id', 'role.role_id')
                ->where('profil_file_id', '==', $profil_id)
                ->fetchAll();
    }

    public function getIdRolesUser($profil_id)
    {
        $data = $this->getRolesUserByProfil($profil_id);
        $out  = [];
        foreach ($data as $value) {
            $out[] = $value[ 'role_id' ];
        }

        return $out;
    }

    public function getProfilsFileByUser($user_id)
    {
        if (!empty($this->profil[ $user_id ])) {
            return $this->profil[ $user_id ];
        }
        $this->profil[ $user_id ] = $this->getProfil($user_id);

        return $this->profil[ $user_id ];
    }
}
