<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUser;

class User extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function account($req)
    {
        if ($user = self::user()->isConnected()) {
            return $this->show($user[ 'user_id' ], $req);
        }

        return new \Soosyze\Components\Http\Response(403);
    }

    public function show($id, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }
        if (!$user[ 'actived' ] && !self::user()->isGranted('user.people.manage')) {
            return $this->get404($req);
        }

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
                ->make('page.content', 'page-user-show.php', $this->pathViews, [
                    'roles'        => self::user()->getRolesUser($id),
                    'user'         => $user,
                    'user_submenu' => self::user()->getUserSubmenu('user.show', $id)
                ]);
    }

    public function create()
    {
        $values = [];
        $this->container->callHook('user.create.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values += $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $roles = self::query()->from('role')->where('role_id', '>', 1)->fetchAll();

        $form = (new FormUser([
            'method'  => 'post',
            'action'  => self::router()->getRoute('user.store'),
            'enctype' => 'multipart/form-data' ], self::file(), self::config()))
            ->setValues($values)
            ->fieldsetInformationsCreate()
            ->fieldsetProfil()
            ->fieldsetPassword()
            ->fieldsetActived()
            ->fieldsetRoles($roles)
            ->submitForm('Save', true);

        $this->container->callHook('user.create.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'class' => 'is-invalid' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('User creation')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'form-user.php', $this->pathViews, [
                    'form' => $form
                ])
                ->make('content.menu_user', 'submenu-user.php', $this->pathViews, [
                    'menu' => []
        ]);
    }

    public function store($req)
    {
        if ($req->isMaxSize()) {
            $_SESSION[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];
            $_SESSION[ 'errors_keys' ]          = [];

            return new Redirect(self::router()->getRoute('user.create'));
        }

        $validator = (new Validator())
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());

        $isEmail = ($user    = self::user()->getUser($validator->getInput('email')))
            ? $user[ 'email' ]
            : '';

        $isUsername = ($user       = self::user()->getUserByUsername($validator->getInput('username')))
            ? $user[ 'username' ]
            : '';

        $validator
            ->addInput('is_email', $isEmail)
            ->addInput('is_username', $isUsername)
            ->setRules([
                'username'         => 'required|string|max:255|!equal:@is_username|to_htmlsc',
                'email'            => 'required|email|max:254|!equal:@is_email|to_htmlsc',
                'picture'          => '!required|image:jpeg,jpg,png|max:200Kb',
                'bio'              => '!required|string|max:255|to_htmlsc',
                'name'             => '!required|string|max:255|to_htmlsc',
                'firstname'        => '!required|string|max:255|to_htmlsc',
                'actived'          => 'bool',
                'password_new'     => 'required|string|regex:' . self::user()->passwordPolicy(),
                'password_confirm' => 'required_with:password_new|string|equal:@password_new',
                'roles'            => '!required|array',
                'token_user_form'  => 'token'
            ])
            ->setLabel([
                'username'         => t('User name'),
                'email'            => t('E-mail'),
                'picture'          => t('Picture'),
                'bio'              => t('Biography'),
                'name'             => t('Name'),
                'firstname'        => t('First name'),
                'actived'          => t('Active'),
                'password_new'     => t('New Password'),
                'password_confirm' => t('Confirmation of the new password'),
                'roles'            => t('User Roles')
            ])
            ->setMessages([
                'password_confirm' => [
                    'equal' => [ 'must' => t(':label is incorrect') ]
                ],
                'email'            => [
                    'equal' => [ 'not' => t('The :value :label is unavailable.') ]
                ],
                'username'         => [
                    'equal' => [ 'not' => t('The :value :label is unavailable.') ]
                ]
        ]);

        $this->container->callHook('user.store.validator', [ &$validator ]);

        $validatorRoles = $this->validRole($validator);

        if ($validator->isValid() && $validatorRoles->isValid()) {
            $data = [
                'username'       => $validator->getInput('username'),
                'email'          => $validator->getInput('email'),
                'bio'            => $validator->getInput('bio'),
                'name'           => $validator->getInput('name'),
                'firstname'      => $validator->getInput('firstname'),
                'password'       => self::auth()->hash($validator->getInput('password_new')),
                'actived'        => (bool) $validator->getInput('actived'),
                'time_installed' => (string) time(),
                'timezone'       => 'Europe/Paris'
            ];

            $this->container->callHook('user.store.before', [ &$validator, &$data ]);
            self::query()->insertInto('user', array_keys($data))
                ->values($data)
                ->execute();

            $user = self::user()->getUser($validator->getInput('email'));

            self::query()
                ->insertInto('user_role', [ 'user_id', 'role_id' ])
                ->values([ $user[ 'user_id' ], 2 ]);

            foreach ($validator->getInput('roles', []) as $role) {
                self::query()->values([ $user[ 'user_id' ], $role ]);
            }
            self::query()->execute();

            $this->savePicture($user[ 'user_id' ], $validator);
            $this->container->callHook('user.store.after', [ &$validator ]);

            return new Redirect(self::router()->getRoute('user.admin'));
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout('picture');
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(self::router()->getRoute('user.create'));
    }

    public function edit($id, $req)
    {
        if (!($values = self::user()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('user.edit.form.data', [ &$values, $id ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method'  => 'post',
            'action'  => self::router()->getRoute('user.update', [ ':id' => $id ]),
            'enctype' => 'multipart/form-data' ], self::file(), self::config()))
            ->setValues($values)
            ->fieldsetInformations()
            ->fieldsetProfil()
            ->fieldsetPassword();

        if (self::user()->isGranted('user.permission.manage')) {
            $roles     = self::query()->from('role')->where('role_id', '>', 1)->orderBy('role_weight')->fetchAll();
            $rolesUser = self::user()->getIdRolesUser($id);
            $form->setValues([ 'roles' => $rolesUser ])
                ->fieldsetActived()
                ->fieldsetRoles($roles);
        }
        $form->submitForm();

        $this->container->callHook('user.edit.form', [ &$form, $values, $id ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'class' => 'is-invalid' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Editing a user')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'form-user.php', $this->pathViews, [
                    'form'         => $form,
                    'user_submenu' => self::user()->getUserSubmenu('user.edit', $id)
                ]);
    }

    public function update($id, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }

        $route = self::router()->getRoute('user.edit', [ ':id' => $id ]);

        if ($req->isMaxSize()) {
            $_SESSION[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];

            $_SESSION[ 'errors_keys' ] = [];

            return new Redirect($route);
        }

        $validator = (new Validator())
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles())
            ->setRules([
                /* max:254 RFC5321 - 4.5.3.1.3. */
                'username'         => 'required|string|max:255|to_htmlsc',
                'email'            => 'required|email|max:254',
                'picture'          => '!required|image:jpeg,jpg,png|max:200Kb',
                'bio'              => '!required|string|max:255|to_htmlsc',
                'name'             => '!required|string|max:255|to_htmlsc',
                'firstname'        => '!required|string|max:255|to_htmlsc',
                'password'         => '!required|string',
                'password_new'     => '!required|string|regex:' . self::user()->passwordPolicy(),
                'password_confirm' => 'required_with:password_new|string|equal:@password_new',
                'actived'          => 'bool',
                'roles'            => '!required|array',
                'token_user_form'  => 'required|token'
            ])
            ->setLabel([
                'username'         => t('User name'),
                'email'            => t('E-mail'),
                'picture'          => t('Picture'),
                'bio'              => t('Biography'),
                'name'             => t('Name'),
                'firstname'        => t('First name'),
                'password'         => t('Password'),
                'password_new'     => t('New Password'),
                'password_confirm' => t('Confirmation of the new password'),
                'actived'          => t('Active'),
                'roles'            => t('User Roles')
            ])
            ->setMessages([
            'password_confirm' => [ 'equal' => [ 'must' => t(':label is incorrect') ] ],
            'password'         => [ 'required' => [ 'must' => t(':label is incorrect') ] ]
        ]);

        /* En cas de modification du username. */
        if ($isUpdateUsername = ($validator->getInput('username') !== $user[ 'username' ])) {
            $isUsername = ($userName   = self::user()->getUserByUsername($validator->getInput('username')))
                ? $userName[ 'username' ]
                : '';
            $validator
                ->addInput('is_username', $isUsername)
                ->addRule('username', 'required|string|max:255|!equal:@is_username|to_htmlsc')
                ->setMessages([
                    'username' => [
                        'equal' => [ 'not' => t('The :value :label is unavailable.') ]
                    ]
            ]);
        }

        /* En cas de modification du email. */
        if ($isUpdateEmail = ($validator->getInput('email') !== $user[ 'email' ])) {
            $isEmail   = ($userEmail = self::user()->getUser($validator->getInput('email')))
                ? $userEmail[ 'email' ]
                : '';
            $validator
                ->addInput('is_email', $isEmail)
                ->addRule('email', 'required|email|max:254|!equal:is_email')
                ->setMessages([
                    'email' => [
                        'equal' => [ 'not' => t('The :value :label is unavailable.') ]
                    ]
            ]);
        }

        if ($isUpdateEmail || $isUpdateUsername) {
            $validator->addRule('password', 'required|string');

            if (!self::auth()->hashVerify($validator->getInput('password'), $user)) {
                $validator->addInput('password', '');
            }
        }

        $this->container->callHook('user.update.validator', [ &$validator, $id ]);

        $grantedRole = self::user()->isGranted('user.people.manage');

        /* Ajoute la règle pour le tableau de roles si l'utilisateur à le droit de les modifier. */
        $isValid = $validator->isValid();

        if ($grantedRole) {
            /* Valide les données du tableau de rôles */
            $validatorRole = $this->validRole($validator);
            /* Ajoute à la validation générale la validation des rôles. */
            $isValid       &= $validatorRole->isValid();
        }

        if ($isValid) {
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
            if ($isUpdateMdp = $validator->getInput('password_new') != '') {
                $value[ 'password' ] = self::auth()->hash($validator->getInput('password_new'));
            }

            $this->container->callHook('user.update.before', [
                &$validator, &$value, $id
            ]);
            self::query()->update('user', $value)->where('user_id', '==', $id)->execute();

            if ($grantedRole) {
                $this->updateRole($validator, $id);
            }

            $this->savePicture($id, $validator);
            $this->container->callHook('user.update.after', [ &$validator, $id ]);

            if (($userCurrent = self::user()->isConnected()) && $userCurrent[ 'user_id' ] == $id) {
                $pwd = $isUpdateMdp
                    ? $validator->getInput('password_new')
                    : $validator->getInput('password');

                self::auth()->login($validator->getInput('email'), $pwd);
            }
            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout('picture');
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if ($grantedRole) {
            $_SESSION[ 'messages' ][ 'errors' ] += $validatorRole->getKeyErrors();
        }

        return new Redirect($route);
    }

    public function remove($id, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('user.remove.form.data', [ &$user, $id ]);

        $form = (new FormBuilder([
            'method' => 'post',
            'action' => self::router()->getRoute('user.delete', [ ':id' => $id ])
            ]))
            ->group('user-edit-information-fieldset', 'fieldset', function ($form) {
                $form->legend('user-edit-information-legend', t('Account deletion'))
                ->html('system-favicon-info-dimensions', '<p:attr>:_content</p>', [
                    '_content' => t('Warning ! The deletion of the user account is final.')
                ]);
            })
            ->token('token_user_remove')
            ->submit('sumbit', t('Delete'), [ 'class' => 'btn btn-danger' ]);

        $this->container->callHook('user.remove.form', [ &$form, $user, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Delete :name account', [ ':name' => $user[ 'username' ] ])
                ])
                ->make('page.content', 'form-user.php', $this->pathViews, [
                    'form'         => $form,
                    'user_submenu' => self::user()->getUserSubmenu('user.remove', $id)
                ]);
    }

    public function delete($id, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'id'                => 'required|int|!equal:1',
                'token_user_remove' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('id', $id);

        $this->container->callHook('user.delete.validator', [ &$validator, $user,
            $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('user.delete.before', [ $validator, $user,
                $id ]);
            self::query()->from('user_role')->where('user_id', '==', $id)->delete()->execute();
            self::query()->from('user')->where('user_id', '==', $id)->delete()->execute();
            $this->container->callHook('user.delete.after', [ $validator, $user,
                $id ]);
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        }

        return new Redirect(self::router()->getRoute('user.admin'));
    }

    protected function validRole(Validator $validator)
    {
        $validatorRoles = new Validator();
        if ($validator->hasError('roles')) {
            $listRoles = implode(',', self::query()->from('role')->lists('role_id'));
            foreach ($validator->getInput('roles', []) as $key => $role) {
                $validatorRoles
                    ->addRule($key, 'int|inarray:' . $listRoles)
                    ->addLabel($key, t($role))
                    ->addInput($key, $key);
            }
        }
        $this->container->callHook('user.update.validator', [ &$validatorRoles ]);

        return $validatorRoles;
    }

    protected function updateRole($validator, $idUser)
    {
        $this->container->callHook('user.update.role.before', [ &$validator, $idUser ]);

        self::query()
            ->from('user_role')
            ->where('user_id', '==', $idUser)
            ->delete()
            ->execute();

        self::query()
            ->insertInto('user_role', [ 'user_id', 'role_id' ])
            ->values([ $idUser, 2 ]);
        foreach (array_keys($validator->getInput('roles', [])) as $idRole) {
            self::query()->values([ $idUser, $idRole ]);
        }
        self::query()->execute();

        $this->container->callHook('user.update.role.after', [ $validator, $idUser ]);
    }

    private function savePicture($id, $validator)
    {
        $dir = self::core()->getSettingEnv('files_public', 'app/files') . "/user/$id";
        $key = 'picture';

        self::file()
            ->add($validator->getInput($key), $validator->getInput("file-name-$key"))
            ->setName($key)
            ->setPath($dir)
            ->setResolvePath()
            ->callGet(function ($key, $name) use ($id) {
                return self::user()->find($id)[ $key ];
            })
            ->callMove(function ($key, $name, $move) use ($id, $dir) {
                self::query()->update('user', [ $key => "$dir/$name" ])->where('user_id', '==', $id)->execute();
            })
            ->callDelete(function ($key, $name) use ($id) {
                self::query()->update('user', [ $key => '' ])->where('user_id', '==', $id)->execute();
            })
            ->save();
    }
}
