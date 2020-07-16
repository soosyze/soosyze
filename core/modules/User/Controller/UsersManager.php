<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Validator\Validator;

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

        $this->hydrateUsersLinks($users);

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
                    'link_filter_user'     => self::router()->getRoute('user.admin.filter'),
                    'link_user_admin'      => self::router()->getRoute('user.admin'),
                    'users'                => $users,
                    'user_manager_submenu' => self::user()->getUserManagerSubmenu('user.admin')
                ]);
    }

    public function filter($req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $validator = (new Validator)
            ->setRules([
                'actived'   => '!required|between_numeric:0,1',
                'firstname' => '!required|string|max:255',
                'name'      => '!required|string|max:255',
                'username'  => '!required|string|max:255',
            ])
            ->setInputs($req->getQueryParams());

        self::query()->from('user');

        if (in_array($validator->getInput('actived'), [ '0', 0, '1', 1 ], true)) {
            self::query()->where('actived', (bool) $validator->getInput('actived'));
        }
        self::query()->where(function ($query) use ($validator) {
            if ($validator->getInput('firstname', '')) {
                $query->orWhere('firstname', 'ilike', '%' . $validator->getInput('firstname') . '%');
            }
            if ($validator->getInput('name', '')) {
                $query->orWhere('name', 'ilike', '%' . $validator->getInput('name') . '%');
            }
            if ($validator->getInput('username', '')) {
                $query->orWhere('username', 'ilike', '%' . $validator->getInput('username') . '%');
            }
        });

        $users = self::query()->fetchAll();

        $this->hydrateUsersLinks($users);

        return self::template()
                ->createBlock('filter-user.php', $this->pathViews)
                ->addVars([ 'users' => $users ]);
    }

    protected function hydrateUsersLinks(&$users)
    {
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
    }
}
