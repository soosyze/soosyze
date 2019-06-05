<?php

namespace SoosyzeCore\User\Controller;

class UsersManagement extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes = CONFIG_USER . 'routing-user_management.json';
    }

    public function admin()
    {
        $users = self::user()->getUsers();
        foreach ($users as &$user) {
            $user[ 'link_show' ]   = self::router()->getRoute('user.show', [
                ':id' => $user[ 'user_id' ]
            ]);
            $user[ 'link_edit' ]   = self::router()->getRoute('user.edit', [
                ':id' => $user[ 'user_id' ]
            ]);
            $user[ 'link_remove' ] = self::router()->getRoute('user.remove', [
                ':id' => $user[ 'user_id' ]
            ]);
            $user[ 'roles' ]       = self::user()->getRolesUser($user[ 'user_id' ]);
        }

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-user"></i> Administrer les utilisateurs'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-user_management.php', VIEWS_USER, [
                    'users'              => $users,
                    'link_add'           => self::router()->getRoute('user.create'),
                    'link_role'          => self::router()->getRoute('user.role.admin'),
                    'link_permission'    => self::router()->getRoute('user.permission.admin'),
                    'granted_permission' => self::user()->isGranted('user.permission.manage'),
        ]);
    }
}
