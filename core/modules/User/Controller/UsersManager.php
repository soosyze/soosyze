<?php

namespace SoosyzeCore\User\Controller;

class UsersManager extends \Soosyze\Controller
{
    protected $pathViews;

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
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
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Administer users')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-user_manager.php', $this->pathViews, [
                    'link_create_user'     => self::router()->getRoute('user.create'),
                    'users'                => $users,
                    'user_manager_submenu' => self::user()->getManagerSubmenu('user.admin')
                ]);
    }

    public function getMenu()
    {
        $menu[] = [
            'title_link' => t('Add a user'),
            'link'       => self::router()->getRoute('user.create')
        ];
        if (self::user()->isGranted('user.permission.manage')) {
            $menu[] = [
                'title_link' => t('Administer roles'),
                'link'       => self::router()->getRoute('user.role.admin')
            ];
            $menu[] = [
                'title_link' => t('Administer permissions'),
                'link'       => self::router()->getRoute('user.permission.admin')
            ];
        }

        self::core()->callHook('user.manage.menu', [ &$menu, self::user() ]);

        return $menu;
    }
}
