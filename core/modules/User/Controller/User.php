<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use User\Form\FormUser;

use SoosyzeCore\User\Form\FormUser;

class User extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing-user.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function account($req)
    {
        if (($user = self::user()->isConnected())) {
            return $this->show($user[ 'user_id' ], $req);
        }

        $route = self::router()->getRoute('user.login');

        return new Redirect($route);
    }

    public function show($id, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }
        if (!$user[ 'actived' ] && !self::user()->isGranted('user.people.manage')) {
            return $this->get404($req);
        }

        $roles = self::user()->getRolesUser($id);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => $user[ 'username' ]
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-user-show.php', $this->pathViews, [
                    'user'  => $user,
                    'roles' => $roles
                ])
                ->render('content.menu_user', 'menu-user.php', $this->pathViews, [
                    'menu' => $this->getMenuUser($id)
        ]);
    }

    public function create()
    {
        $data = [ 'username' => '', 'email' => '', 'firstname' => '', 'name' => '' ];
        $this->container->callHook('user.create.form.data', [ &$data ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $roles = self::query()->from('role')->where('role_id', '>', 1)->fetchAll();

        $form = (new FormUser([
            'method'  => 'post',
            'action'  => self::router()->getRoute('user.store'),
            'enctype' => 'multipart/form-data' ], self::file()))
            ->content($data)
            ->fieldsetInformationsCreate()
            ->fieldsetProfil()
            ->fieldsetPassword()
            ->fieldsetActived()
            ->fieldsetRoles($roles)
            ->submitForm();

        $this->container->callHook('user.create.form', [ &$form, $data ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-user"></i>  Création de l’utilisateur'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'form-user.php', $this->pathViews, [
                    'form' => $form
                ])->render('content.menu_user', 'menu-user.php', $this->pathViews, [
                'menu' => []
        ]);
    }

    public function store($req)
    {
        $post   = $req->getParsedBody();
        $files  = $req->getUploadedFiles();
        $server = $req->getServerParams();

        if (empty($post) && empty($files) && isset($server[ 'CONTENT_LENGTH' ]) && $server[ 'CONTENT_LENGTH' ] > 0) {
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'La quantité totales des données reçues '
                . 'dépasse la valeur maximale autorisée par la directive post_max_size '
                . 'de votre fichier php.ini' ];
            $_SESSION[ 'errors_keys' ]          = [];

            return new Redirect(self::router()->getRoute('user.create'));
        }

        $validator = (new Validator())
            ->setRules([
                'username'         => 'required|string|max:255|htmlsc',
                'email'            => 'required|email|max:254|htmlsc',
                'picture'          => '!required|image|max:200000',
                'bio'              => '!required|string|max:255|htmlsc',
                'name'             => '!required|string|max:255|htmlsc',
                'firstname'        => '!required|string|max:255|htmlsc',
                'actived'          => 'bool',
                'password_new'     => 'required|string|regex:' . self::user()->passwordPolicy(),
                'password_confirm' => 'required_with:password_new|string|equal:@password_new',
                'role'             => '!required|array',
                'token'            => 'token'
            ])
            ->setInputs($post + $files);

        if (isset($post[ 'role' ])) {
            $roles = implode(',', self::query()->from('role')->where('role_id', '>', 2)->lists('role_id'));
            foreach ($post[ 'role' ] as $role) {
                $validator->addInput('role-' . $role, $role)
                    ->addRule('role-' . $role, 'int|inarray:' . $roles);
            }
        }

        $is_email    = self::user()->getUser($validator->getInput('email'));
        $is_username = self::query()->from('user')
                ->where('username', $validator->getInput('username'))->fetch();

        $this->container->callHook('user.store.validator', [ &$validator ]);

        if ($validator->isValid() && !$is_email && !$is_username) {
            $salt        = md5(time());
            $passworHash = self::user()->hashSession($validator->getInput('password_new'), $salt);
            $data        = [
                'username'       => $validator->getInput('username'),
                'email'          => $validator->getInput('email'),
                'bio'            => $validator->getInput('bio'),
                'name'           => $validator->getInput('name'),
                'firstname'      => $validator->getInput('firstname'),
                'password'       => self::user()->hash($passworHash),
                'salt'           => $salt,
                'actived'        => (bool) $validator->getInput('actived'),
                'time_installed' => (string) time(),
                'timezone'       => 'Europe/Paris'
            ];

            $this->container->callHook('user.store.before', [ &$validator, &$data ]);
            self::query()->insertInto('user', array_keys($data))
                ->values($data)
                ->execute();

            $user = self::user()->getUser($validator->getInput('email'));
            self::query()->insertInto('user_role', [ 'user_id', 'role_id' ])
                ->values([ $user[ 'user_id' ], 2 ]);
            if (isset($post[ 'role' ])) {
                foreach ($post[ 'role' ] as $role) {
                    self::query()->values([ $user[ 'user_id' ], $role ]);
                }
            }
            self::query()->execute();
            $this->savePicture($user[ 'user_id' ], $validator);
            $this->container->callHook('user.store.after', [ &$validator ]);

            $route = self::router()->getRoute('user.management.admin');

            return new Redirect($route);
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout('picture');
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if ($is_email) {
            $_SESSION[ 'messages' ][ 'errors' ][] = 'L\'email <i>' . $validator->getInput('email') . '</i> est indisponible.';
            $_SESSION[ 'errors_keys' ][]          = 'email';
        }
        if ($is_username) {
            $_SESSION[ 'messages' ][ 'errors' ][] = 'Le nom d\'utilisateur <i>' . $validator->getInput('username') . '</i> est indisponible.';
            $_SESSION[ 'errors_keys' ][]          = 'username';
        }

        $route = self::router()->getRoute('user.create');

        return new Redirect($route);
    }

    public function edit($id, $req)
    {
        if (!($data = self::user()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('user.edit.form.data', [ &$data, $id ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method'  => 'post',
            'action'  => self::router()->getRoute('user.update', [ ':id' => $id ]),
            'enctype' => 'multipart/form-data' ], self::file(), self::config()))
            ->content($data)
            ->fieldsetInformations()
            ->fieldsetProfil()
            ->fieldsetPassword();
        if (self::user()->isGranted('user.permission.manage')) {
            $roles      = self::query()->from('role')->where('role_id', '>', 1)->orderBy('role_weight')->fetchAll();
            $roles_user = self::user()->getIdRolesUser($id);
            $form->fieldsetActived()->fieldsetRoles($roles, $roles_user);
        }
        $form->submitForm();

        $this->container->callHook('user.edit.form', [ &$form, $data, $id ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-user"></i> Édition de l’utilisateur'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'form-user.php', $this->pathViews, [
                    'form' => $form
                ])->render('content.menu_user', 'menu-user.php', $this->pathViews, [
                'menu' => $this->getMenuUser($id)
        ]);
    }

    public function udpate($id, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }

        $post   = $req->getParsedBody();
        $files  = $req->getUploadedFiles();
        $server = $req->getServerParams();
        $route  = self::router()->getRoute('user.edit', [ ':id' => $id ]);

        if (empty($post) && empty($files) && isset($server[ 'CONTENT_LENGTH' ]) && $server[ 'CONTENT_LENGTH' ] > 0) {
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'La quantité totales des données reçues '
                . 'dépasse la valeur maximale autorisée par la directive post_max_size '
                . 'de votre fichier php.ini' ];
            $_SESSION[ 'errors_keys' ]          = [];

            return new Redirect($route);
        }

        $validator = (new Validator())
            ->setRules([
                /* max:254 RFC5321 - 4.5.3.1.3. */
                'username'         => 'required|string|max:255|htmlsc',
                'email'            => 'required|email|max:254',
                'picture'          => '!required|image|max:200000',
                'bio'              => '!required|string|max:255|htmlsc',
                'name'             => '!required|string|max:255|htmlsc',
                'firstname'        => '!required|string|max:255|htmlsc',
                'password_new'     => '!required|string|regex:' . self::user()->passwordPolicy(),
                'password_confirm' => 'required_with:password_new|string|equal:@password_new',
                'actived'          => 'bool',
                'token'            => 'required|token'
            ])
            ->setInputs($post + $files);

        $is_email      = $is_username   = false;
        /* En cas de modification du email. */
        if (($isUpdateEmail = $validator->getInput('email') !== $user[ 'email' ])) {
            $is_email = self::user()->getUser($validator->getInput('email'));
            $password = $validator->getInput('password');
            $verify   = self::user()->hashVerify($password, $user);
            $validator->addInput('password', '');
            if (!$verify) {
                $validator->addRule('password', 'required');
            }
        }
        if ($validator->getInput('username') !== $user[ 'username' ]) {
            $is_username = self::query()->from('user')
                    ->where('username', $validator->getInput('username'))->fetch();
        }

        $this->container->callHook('user.update.validator', [ &$validator, $id ]);

        if ($validator->isValid() && !$is_email && !$is_username) {
            /* Prépare les donnée à mettre à jour. */
            $value = [
                'username'  => $validator->getInput('username'),
                'email'     => $validator->getInput('email'),
                'bio'       => $validator->getInput('bio'),
                'name'      => $validator->getInput('name'),
                'firstname' => $validator->getInput('firstname')
            ];

            /* Si l'utilisateur à les droits d'administrer les autres utilisateurs. */
            if (self::user()->isGranted('user.people.manage')) {
                $value[ 'actived' ] = (bool) $validator->getInput('actived');
            }

            /* En cas de modification du mot de passe. */
            if (($isUpdateMdp = $validator->getInput('password_new') != '')) {
                $passwordHash        = self::user()->hashSession($validator->getInput('password_new'), $user[ 'salt' ]);
                $value[ 'password' ] = self::user()->hash($passwordHash);
            }

            $this->container->callHook('user.update.before', [ &$validator, &$value,
                $id ]);
            self::query()->update('user', $value)->where('user_id', '==', $id)->execute();
            if (self::user()->isGranted('user.people.manage')) {
                $this->updateRole($id, $req);
            }
            $this->savePicture($id, $validator);
            $this->container->callHook('user.update.after', [ &$validator, $id ]);

            $user_current = self::user()->isConnected();
            if ($isUpdateEmail && $user_current[ 'user_id' ] == $id) {
                $user = self::user()->find($id);
                self::user()->login($user[ 'email' ], $password);
            }
            if ($isUpdateMdp && $user_current[ 'user_id' ] == $id) {
                $user = self::user()->find($id);
                self::user()->login($user[ 'email' ], $validator->getInput('password_new'));
            }
            $_SESSION[ 'messages' ][ 'success' ] = [ 'Configuration Enregistrée' ];

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout('picture');
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if ($is_email) {
            $_SESSION[ 'messages' ][ 'errors' ][] = 'L\'email <i>' . $validator->getInput('email') . '</i> est indisponible.';
            $_SESSION[ 'errors_keys' ][]          = 'email';
        }
        if ($is_username) {
            $_SESSION[ 'messages' ][ 'errors' ][] = 'Le nom d\'utilisateur <i>' . $validator->getInput('username') . '</i> est indisponible.';
            $_SESSION[ 'errors_keys' ][]          = 'username';
        }

        return new Redirect($route);
    }

    public function remove($id, $req)
    {
        if (!($data = self::user()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('user.remove.form.data', [ &$data, $id ]);

        $form = (new FormBuilder([
            'method' => 'post',
            'action' => self::router()->getRoute('user.delete', [ ':id' => $id ])
            ]))
            ->group('user-edit-information-fieldset', 'fieldset', function ($form) {
                $form->legend('user-edit-information-legend', 'Suppression de compte')
                ->html('system-favicon-info-dimensions', '<p:css:attr>:_content</p>', [
                    '_content' => 'Attention ! La suppression du compte utilisateur est définitif.'
                ]);
            })->token()
            ->submit('sumbit', 'Supprimer le compte', [ 'class' => 'btn btn-danger' ]);

        $this->container->callHook('user.remove.form', [ &$form, $data, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-user"></i> Supprimer du compte de <i>' . $data[ 'username' ] . '</i>'
                ])
                ->render('page.content', 'form-user.php', $this->pathViews, [
                    'form' => $form
                ])->render('content.menu_user', 'menu-user.php', $this->pathViews, [
                'menu' => $this->getMenuUser($id)
        ]);
    }

    public function delete($id, $req)
    {
        if (!($query = self::user()->find($id))) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'id'    => 'required|int|!equal:1',
                'token' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('id', $id);

        $this->container->callHook('user.delete.validator', [ &$validator, $query, $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('user.delete.before', [ $validator, $query, $id ]);
            self::query()->from('user_role')->where('user_id', '==', $id)->delete()->execute();
            self::query()->from('user')->where('user_id', '==', $id)->delete()->execute();
            $this->container->callHook('user.delete.after', [ $validator, $query, $id ]);
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        }

        return new Redirect(self::router()->getRoute('user.management.admin'));
    }

    protected function updateRole($idUser, $req)
    {
        $roles          = implode(',', self::query()->from('role')->where('role_id', '>', 2)->lists('role_id'));
        $post           = $req->getParsedBody();
        $data[ 'role' ] = isset($post[ 'role' ])
            ? $post[ 'role' ]
            : [];
        $validator      = new Validator();

        foreach ($data[ 'role' ] as $idRole) {
            $validator->addInput('role-' . $idRole, $idRole)
                ->addRule('role-' . $idRole, 'int|inarray:' . $roles);
        }

        if ($validator->isValid()) {
            $this->container->callHook('user.update.role.before', [ &$validator,
                &$data, $idUser ]);
            self::query()->from('user_role')->where('user_id', '==', $idUser)->delete()->execute();
            self::query()->insertInto('user_role', [ 'user_id', 'role_id' ])
                ->values([ $idUser, 2 ]);
            foreach ($data[ 'role' ] as $idRole) {
                self::query()->values([ $idUser, $idRole ]);
            }
            self::query()->execute();
            $this->container->callHook('user.update.role.after', [ &$validator, &$data,
                $idUser ]);

            return true;
        }
        $_SESSION[ 'messages' ][ 'errors' ] += $validator->getErrors();

        return false;
    }

    protected function getMenuUser($id)
    {
        $menu[] = [
            'title_link' => 'Voir',
            'link'       => self::router()->getRoute('user.show', [ ':id' => $id ])
        ];
        if (self::user()->isGranted('user.people.manage') || self::user()->isGranted('user.edited')) {
            $menu[] = [
                'title_link' => 'Éditer',
                'link'       => self::router()->getRoute('user.edit', [ ':id' => $id ])
            ];
        }
        if (self::user()->isGranted('user.people.manage') || self::user()->isGranted('user.deleted')) {
            $menu[] = [
                'title_link' => 'Supprimer',
                'link'       => self::router()->getRoute('user.remove', [ ':id' => $id ])
            ];
        }

        return $menu;
    }

    private function savePicture($id, $validator)
    {
        $dir = self::core()->getSetting('files_public') . "/user/$id";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $key = 'picture';
        self::file()
            ->add($validator->getInput($key), $validator->getInput("file-name-$key"))
            ->moveTo($key, $dir)
            ->callGet(function ($key) use ($id) {
                return self::user()->find($id)[ $key ];
            })
            ->callMove(function ($key, $move) use ($id) {
                self::query()->update('user', [ $key => $move ])->where('user_id', '==', $id)->execute();
            })
            ->callDelete(function ($key) use ($id) {
                self::query()->update('user', [ $key => '' ])->where('user_id', '==', $id)->execute();
            })
            ->save();
    }
}
