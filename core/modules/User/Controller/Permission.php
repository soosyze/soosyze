<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Http\Redirect;

class Permission extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing-permission.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin($req)
    {
        /* Récupère toutes les permissions par module. */
        $modules = [];
        self::core()->callHook('user.permission.module', [ &$modules ]);
        ksort($modules);

        /* Récupére les pérmissions et role en base de données. */
        $permissions_by_role = self::query()
            ->select('permission_id', 'role_id')
            ->from('role')
            ->leftJoin('role_permission', 'role_id', 'role_permission.role_id')
            ->fetchAll();
        $roles               = self::query()->from('role')->orderBy('role_weight')->fetchAll();

        /* Simplifie les permissions par roles. */
        foreach ($permissions_by_role as $value) {
            $tmp[ $value[ 'permission_id' ] ][] = $value[ 'role_id' ];
        }
        $permissions_by_role = $tmp;

        /* Met en forme les droit utilisateurs. */
        $output = [];
        foreach ($modules as $key_module => $module) {
            foreach ($module as $key_permission => $permission) {
                $output[ $key_module ][ $key_permission ][ 'action' ] = $permission;
                foreach ($roles as $role) {
                    $output[ $key_module ][ $key_permission ][ 'roles' ][ $role[ 'role_id' ] ] = '';
                    if (isset($permissions_by_role[ $key_permission ]) && in_array($role[ 'role_id' ], $permissions_by_role[ $key_permission ])) {
                        $output[ $key_module ][ $key_permission ][ 'roles' ][ $role[ 'role_id' ] ] = 'checked';
                    }
                }
            }
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-user"></i> Administrer les permissions'
                ])
                ->render('page.content', 'page-permission.php', $this->pathViews, [
                    'link_update' => self::router()->getRoute('user.permission.update'),
                    'roles'       => $roles,
                    'colspan'     => count($roles) + 1,
                    'modules'     => $output
        ]);
    }

    public function udpate($req)
    {
        $post = $req->getParsedBody();

        $roles_id            = self::query()->from('role')->lists('role_id');
        $permissions_by_role = self::query()->from('role_permission')->fetchAll();

        foreach ($roles_id as $id) {
            $perm[ $id ] = [];
            if (empty($post[ $id ])) {
                $post[ $id ] = [];
            }
            foreach ($permissions_by_role as $permission) {
                $perm[ $permission[ 'role_id' ] ][ $permission[ 'permission_id' ] ] = $permission[ 'permission_id' ];
            }
            $this->storePermission($id, $perm[ $id ], $post[ $id ]);
            $this->deletePermission($id, $perm[ $id ], $post[ $id ]);
        }

        $route = self::router()->getRoute('user.permission.admin');

        return new Redirect($route);
    }

    protected function storePermission(
        $idRole,
        array $permission,
        array $newPermission
    ) {
        if (($diff_create = array_diff_key($newPermission, $permission))) {
            self::query()->insertInto('role_permission', [ 'role_id', 'permission_id' ]);
            foreach ($diff_create as $create) {
                if (!self::user()->hasPermission($create)) {
                    continue;
                }
                self::query()->values([ $idRole, $create ]);
            }
            self::query()->execute();
        }
    }

    protected function deletePermission(
        $idRole,
        array $permission,
        array $newPermission
    ) {
        if (!($diff_delete = array_diff_key($permission, $newPermission))) {
            return null;
        }
        self::query()->from('role_permission')->delete();
        foreach ($diff_delete as $delete) {
            self::query()->orWhere(function ($query) use ($idRole, $delete) {
                $query->where('role_id', '==', $idRole)
                    ->where('permission_id', '==', $delete);
            });
        }
        self::query()->execute();
    }
}
