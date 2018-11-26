<?php

namespace User\Services;

use Soosyze\Components\Http\Reponse;
use Soosyze\Components\Http\Stream;

define('BDD_USER', 'user');
define('BDD_PERMISSIONS', 'permissions');

class User
{
    protected $query;

    protected $routing;

    protected $core;

    public function __construct($query, $routing, $core)
    {
        $this->query   = $query;
        $this->routing = $routing;
        $this->core    = $core;
    }

    public function find($id)
    {
        return $this->query
                ->from('user')
                ->where('user_id', '==', $id)
                ->fetch();
    }

    public function getUser($email)
    {
        return $this->query
                ->from('user')
                ->where('email', $email)
                ->fetch();
    }

    public function getPermission($key)
    {
        return $this->query
                ->from('permission')
                ->where('permission_id', '==', $key)
                ->fetch();
    }

    public function getGranted($user, $permission)
    {
        return $this->query
                ->from('user')
                ->leftJoin('user_role', 'user_id', 'user_role.user_id')
                ->leftJoin('role', 'role_id', 'role.role_id')
                ->leftJoin('role_permission', 'role_id', 'role_permission.role_id')
                ->leftJoin('permission', 'permission_id', 'permission.permission_id')
                ->where('permission_id', $permission)
                ->where('user_id', $user[ 'user_id' ])
                ->fetch();
    }

    public function getGrantedAnonymous($permission)
    {
        return $this->query
                ->from('role')
                ->leftJoin('role_permission', 'role_id', 'role_permission.role_id')
                ->leftJoin('permission', 'permission_id', 'permission.permission_id')
                ->where('permission_id', $permission)
                ->where('role_name', 'user_anonyme')
                ->fetch();
    }

    /**
     * Créer la session et les token d'identification.
     *
     * @param string $login
     * @param string $password
     *
     * @return bool
     */
    public function login($login, $password)
    {
        if ('' == session_id()) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure'   => true,
            ]);
        }

        if (($user = $this->getUser($login))) {
            $passwordHash = hash('sha256', $password . $user[ 'salt' ]);
            if ($passwordHash == $user[ 'password' ]) {
                $_SESSION[ 'token_user' ]     = $login;
                $_SESSION[ 'token_password' ] = $passwordHash;

                return true;
            }
        }

        return false;
    }

    /**
     * Créer la session et les token d'identification.
     *
     * @param type $login
     * @param type $password
     *
     * @return bool
     */
    public function relogin($login, $password)
    {
        if (session_id() == '') {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure'   => true
            ]);
        }
        $user = $this->getUser($login);
        if ($user) {
            $_SESSION[ 'token_user' ]     = $login;
            $_SESSION[ 'token_password' ] = $password;

            return true;
        }

        return false;
    }

    /**
     * Si la session existe renvoie l'utilisateur,
     * sinon s'il y a correspondance dans les autres cas renvoie faux.
     *
     * @return bool
     */
    public function isConnected()
    {
        if (!empty($_SESSION[ 'token_user' ]) && !empty($_SESSION[ 'token_password' ])) {
            $user = $this->getUser($_SESSION[ 'token_user' ]);
            if ($user) {
                return $_SESSION[ 'token_password' ] == $user[ 'password' ]
                    ? $user
                    : false;
            }
        }

        return false;
    }

    /**
     * Vérifie les droits d'accès aux contrôleurs.
     *
     * @param type $key
     * @param type $grant
     *
     * @return type
     */
    public function isGranted($key, &$grant = false)
    {
        $permission = $this->getPermission($key);

        /* Si la route ne contient pas de permission. */
        if (!$permission) {
            $grant = true;
        } elseif (($user = $this->isConnected())) {
            $grant = (bool) $this->getGranted($user, $permission[ 'permission_id' ]);
        } else {
            $grant = (bool) $this->getGrantedAnonymous($permission[ 'permission_id' ]);
        }

        return $grant;
    }

    /**
     * Fonctionnement par défaut de l'application.
     * Défini les règles du déclenchement d'un retour 403 à l'aide des hooks.
     *
     * @param Request $request
     * @param Reponse $reponse
     *
     * @return Reponse
     */
    public function hookReponseBefore(&$request, &$reponse)
    {
        $route = $this->routing->parse($request);

        if (!$this->isGranted($route[ 'key' ])) {
            $reponse = new Reponse(403, new Stream('Erreur HTTP 403 Forbidden'));
        }
    }
}
