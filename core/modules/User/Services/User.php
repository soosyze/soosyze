<?php

namespace SoosyzeCore\User\Services;

use Soosyze\Components\Http\Response;
use Soosyze\Components\Http\Stream;

class User
{
    /**
     * Dépendance du service.
     * @var \QueryBuilder\Services\Query
     */
    private $query;

    /**
     * Dépendance du service.
     * @var \Soosyze\Router
     */
    private $router;

    /**
     * Dépendance du service.
     * @var \Soosyze\App
     */
    private $core;

    /**
     * La liste des permissions pour l'utilisateur courant.
     * @var array
     */
    private $granted = [];

    /**
     * La liste des permissions
     * @var array
     */
    private $permissions = [];

    /**
     * Les données utilisateur courant ou false.
     * @var bool|array
     */
    private $connect = false;

    public function __construct($query, $router, $core)
    {
        $this->query  = $query;
        $this->router = $router;
        $this->core   = $core;
    }

    public function find($id)
    {
        return $this->query->from('user')
                ->where('user_id', '==', $id)
                ->fetch();
    }

    public function findActived($id, $actived = true)
    {
        return $this->query->from('user')
                ->where('user_id', '==', $id)
                ->where('actived', $actived)
                ->fetch();
    }

    public function getUser($email)
    {
        return $this->query->from('user')
                ->where('email', $email)
                ->fetch();
    }

    public function getUserActived($email, $actived = true)
    {
        return $this->query->from('user')
                ->where('email', $email)
                ->where('actived', $actived)
                ->fetch();
    }

    public function getUserActivedToken($token, $actived = true)
    {
        return $this->query->from('user')
                ->where('token_connected', $token)
                ->where('actived', $actived)
                ->fetch();
    }

    public function getUsers()
    {
        return $this->query->from('user')->fetchAll();
    }

    public function getRolesUser($idUser)
    {
        return $this->query->from('user_role')
                ->leftJoin('role', 'role_id', 'role.role_id')
                ->where('user_id', '==', $idUser)
                ->fetchAll();
    }

    public function getRoles()
    {
        return $this->query
            ->from('role')
            ->fetchAll();
    }
    
    public function getIdRolesUser($idUser)
    {
        $data = $this->query->from('user_role')
                ->leftJoin('role', 'role_id', 'role.role_id')
                ->where('user_id', '==', $idUser)
                ->fetchAll();
        $out = [];
        foreach ($data as $value) {
            $out[$value['role_id']] = $value['role_label'];
        }

        return $out;
    }

    public function hasPermission($idPermission)
    {
        if (!empty($this->permissions)) {
            return isset($this->permissions[ $idPermission ]);
        }

        $permission = [];
        $this->core->callHook('user.permission.module', [ &$permission ]);
        foreach ($permission as $value) {
            $this->permissions += $value;
        }

        return isset($this->permissions[ $idPermission ]);
    }

    public function getGranted($user, $idPermission)
    {
        if (!empty($this->granted)) {
            return in_array($idPermission, $this->granted);
        }

        $this->granted = $this->query->from('user_role')
            ->leftJoin('role', 'role_id', 'role.role_id')
            ->leftJoin('role_permission', 'role_id', 'role_permission.role_id')
            ->where('user_id', $user[ 'user_id' ])
            ->lists('permission_id');

        return in_array($idPermission, $this->granted);
    }

    public function getGrantedAnonymous($idPermission)
    {
        if (!empty($this->granted)) {
            return in_array($idPermission, $this->granted);
        }

        $this->granted = $this->query->from('role_permission')
            ->where('role_id', 1)
            ->lists('permission_id');

        return in_array($idPermission, $this->granted);
    }

    /**
     * Si la session existe renvoie l'utilisateur,
     * sinon s'il y a correspondance dans les autres cas renvoie faux.
     *
     * @return bool|array
     */
    public function isConnected()
    {
        if ($this->connect) {
            return $this->connect;
        }
        if (!empty($_SESSION[ 'token_connected' ])) {
            if (!($user = $this->getUserActivedToken($_SESSION[ 'token_connected' ]))) {
                return false;
            }

            $this->connect = $_SESSION[ 'token_connected' ] == $user['token_connected']
                ? $user
                : false;

            return $this->connect;
        }

        return false;
    }

    public function passwordPolicy()
    {
        if (($length  = (int) $this->core->get('config')->get('settings.password_length', 8)) < 8) {
            $length = 8;
        }
        if (($upper  = (int) $this->core->get('config')->get('settings.password_upper', 1)) < 1) {
            $upper = 1;
        }
        if (($digit  = (int) $this->core->get('config')->get('settings.password_digit', 1)) < 1) {
            $digit = 1;
        }
        if (($special  = (int) $this->core->get('config')->get('settings.password_special', 1)) < 1) {
            $special = 1;
        }

        return '/(?=.*\d){' . $digit . ',}(?=.*[a-z])(?=.*\W){' . $special . ',}(?=.*[A-Z]){' . $upper . ',}.{' . $length . ',}/';
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
        /* Si la permission n'existe pas. */
        if (!$this->hasPermission($key)) {
            $grant = true;
        }
        /* Si l'utilisateur et connecté. */
        elseif ($user = $this->isConnected()) {
            $grant = (bool) $this->getGranted($user, $key);
        }
        /* Si l'utilisateur annonyme peut voir la route. */
        else {
            $grant = (bool) $this->getGrantedAnonymous($key);
        }

        return $grant;
    }

    public function isGrantedRoute($request)
    {
        $route = $this->router->parse($request);
        
        /* Si la permission n'existe pas. */
        if ($this->hasPermission($route[ 'key' ])) {
            return $this->isGranted($route[ 'key' ]);
        }

        if (isset($route[ 'with' ])) {
            $query  = $this->router->parseQueryFromRequest($request);
            $params = $this->router->parseParam($route[ 'path' ], $query, $route[ 'with' ]);
        }

        $params[]    = $request;
        $params[]    = $this->isConnected();
        $permissions = $this->core->callHook('route.' . $route[ 'key' ], $params);

        return $this->isGrantedPermission($permissions);
    }
    
    public function isGrantedPermission($permissions)
    {
        if (\is_bool($permissions)) {
            return $permissions;
        }
        if (\is_string($permissions)) {
            return $this->isGranted($permissions);
        }
        foreach ($permissions as $permission) {
            if ($this->isGranted($permission)) {
                return true;
            }
        }

        return false;
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
        if (!$this->isGrantedRoute($request)) {
            $response = new Response(403, new Stream('Error HTTP 403 Forbidden'));
        }
    }

    public function hookReponseAfter($request, &$response)
    {
        if ($response instanceof \SoosyzeCore\Template\Services\Templating) {
            $vendor = $this->core->getPath('modules', 'modules/core', false) . '/User/Assets/js/script.js';
            $script = $response->getBlock('this')->getVar('scripts');
            $script .= '<script src="' . $vendor . '"></script>';
            $response->view('this', [ 'scripts' => $script ]);
        }
    }
}
