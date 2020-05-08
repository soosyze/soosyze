<?php

namespace SoosyzeCore\User\Services;

class HookUser
{
    /**
     * @var \Soosyze\Config
     */
    protected $config;

    /**
     * @var User
     */
    protected $user;

    public function __construct($config, $user)
    {
        $this->config = $config;
        $this->user   = $user;
    }

    public function hookPermission(&$permission)
    {
        $permission[ 'User' ] = [
            'user.people.manage'     => t('Administer users'),
            'user.permission.manage' => t('Administer permissions'),
            'user.showed'            => t('View user profiles'),
            'user.edited'            => t('Edit your user account'),
            'user.deleted'           => t('Delete your user account'),
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
        if ($id == $user[ 'user_id' ]) {
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

    public function hookLogin($url, $req, $user)
    {
        if ($this->user->isConnectUrl($url)) {
            return false;
        }

        return empty($user);
    }

    public function hookLoginCheck($url, $req, $user)
    {
        if ($this->user->isConnectUrl($url)) {
            return false;
        }
        /* Si le site est en maintenance. */
        if (!$this->config->get('settings.maintenance')) {
            return empty($user);
        }
        /* Et que l'utilisateur qui se connect existe. */
        $post = $req->getParsedBody();
        if (!isset($post[ 'email' ]) || !filter_var($post[ 'email' ], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (!($userActived = $this->user->getUserActived($post[ 'email' ]))) {
            return false;
        }
        /* Si l'utilisateur à le droit de se connecter en mode maintenance. */
        return $this->user->getGranted($userActived, 'system.config.maintenance');
    }

    public function hookLogout($req, $user)
    {
        return !empty($user);
    }

    public function hookRelogin($url, $req, $user)
    {
        if ($this->user->isConnectUrl($url)) {
            return false;
        }

        return empty($user) && $this->config->get('settings.user_relogin');
    }
}
