<?php

namespace User\Services;

class HookUser
{
    /**
     * @var \Soosyze\Config
     */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function hookPermission(&$permission)
    {
        $permission[ 'User' ] = [
            'user.config.manage'     => 'Administrer les configurations',
            'user.people.manage'     => 'Administrer les utilisateurs',
            'user.permission.manage' => 'Administrer les droits',
            'user.showed'            => 'Voir les profils utilisateurs',
            'user.edited'            => 'Modifier son compte utilisateur',
            'user.deleted'           => 'Supprimer son compte utilisateur',
        ];
    }

    public function hookPermissionAdminister()
    {
        return 'user.permission.manage';
    }

    public function hookPeopleAdminister()
    {
        return 'user.people.manage';
    }

    public function hookUserShow($id, $req, $user)
    {
        if ($id == $user[ 'user_id' ]) {
            return true;
        }

        return [ 'user.people.manage', 'user.showed' ];
    }

    public function hookUserEdited($id, $req, $user)
    {
        $output[] = 'user.people.manage';
        if ($id === $user[ 'user_id' ]) {
            $output[] = 'user.edited';
        }

        return $output;
    }

    public function hookUserDeleted($id, $req, $user)
    {
        $output[] = 'user.people.manage';
        if ($id == $user[ 'user_id' ]) {
            $output[] = 'user.deleted';
        }

        return $output;
    }

    public function hookRegister($req, $user)
    {
        return empty($user) && $this->config->get('settings.user_register');
    }

    public function hookActivate($id, $token, $req, $user)
    {
        return empty($user) && $this->config->get('settings.user_register');
    }

    public function hookLogin($req, $user)
    {
        return empty($user);
    }

    public function hookLogout($req, $user)
    {
        return !empty($user);
    }

    public function hookRelogin($req, $user)
    {
        return empty($user) && $this->config->get('settings.user_relogin');
    }
}
