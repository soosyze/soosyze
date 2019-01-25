<?php

namespace User\Services;

use Soosyze\Components\Http\Response;
use Soosyze\Components\Http\Stream;

class User
{
    protected $query;

    protected $routing;

    protected $core;

    protected $grantedA = [];

    protected $granted = [];

    protected $permissions = [];

    private $connect = false;

    public function __construct($query, $routing, $core)
    {
        $this->query   = $query;
        $this->routing = $routing;
        $this->core    = $core;
    }

    public function find($id, $actived = true)
    {
        return $this->query
                ->from('user')
                ->where('user_id', '==', $id)
                ->where('actived', $actived)
                ->fetch();
    }

    public function getUser($email, $actived = true)
    {
        return $this->query
                ->from('user')
                ->where('email', $email)
                ->where('actived', $actived)
                ->fetch();
    }

    public function getPermission($key)
    {
        if (!empty($this->permissions)) {
            return in_array($key, $this->permissions);
        }

        $this->permissions = $this->query
            ->from('permission')
            ->lists('permission_id');

        return in_array($key, $this->permissions);
    }

    public function getGranted($user, $permission)
    {
        if (!empty($this->granted)) {
            return in_array($permission, $this->granted);
        }

        $this->granted = $this->query
            ->from('user')
            ->leftJoin('user_role', 'user_id', 'user_role.user_id')
            ->leftJoin('role', 'role_id', 'role.role_id')
            ->leftJoin('role_permission', 'role_id', 'role_permission.role_id')
            ->leftJoin('permission', 'permission_id', 'permission.permission_id')
            ->where('user_id', $user[ 'user_id' ])
            ->lists('permission_id');

        return in_array($permission, $this->granted);
    }

    public function getGrantedAnonymous($permission)
    {
        if (!empty($this->grantedA)) {
            return in_array($permission, $this->grantedA);
        }

        $this->grantedA = $this->query
            ->from('role')
            ->leftJoin('role_permission', 'role_id', 'role_permission.role_id')
            ->leftJoin('permission', 'permission_id', 'permission.permission_id')
            ->where('role_name', 'user_anonyme')
            ->lists('permission_id');

        return in_array($permission, $this->grantedA);
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
            $passwordHash = $this->hashSession($password, $user[ 'salt' ]);
            if (password_verify($passwordHash, $user[ 'password' ])) {
                $_SESSION[ 'token_user' ]     = $login;
                $_SESSION[ 'token_password' ] = $passwordHash;

                return true;
            }
        }

        return false;
    }
    
    public function hash_verify($password, array $user)
    {
        $passwordHash = $this->hashSession($password, $user[ 'salt' ]);

        return password_verify($passwordHash, $user[ 'password' ]);
    }

    public function hash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function hashSession($password, $salt = '')
    {
        return hash('sha256', $password . $salt);
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
                if ($this->connect) {
                    return $this->connect;
                }
                $this->connect = password_verify($_SESSION[ 'token_password' ], $user[ 'password' ])
                    ? $user
                    : false;

                return $this->connect;
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
            $grant = (bool) $this->getGranted($user, $key);
        } else {
            $grant = (bool) $this->getGrantedAnonymous($key);
        }

        return $grant;
    }

    /**
     * Fonctionnement par défaut de l'application.
     * Défini les règles du déclenchement d'un retour 403 à l'aide des hooks.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function hookResponseBefore(&$request, &$response)
    {
        $route = $this->routing->parse($request);

        if (!$this->isGranted($route[ 'key' ])) {
            $response = new Response(403, new Stream('Erreur HTTP 403 Forbidden'));
        }
    }
}
