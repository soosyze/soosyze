<?php

declare(strict_types=1);

namespace SoosyzeCore\User\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Response;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUser;

/**
 * @method \SoosyzeCore\System\Services\Alias        alias()
 * @method \SoosyzeCore\User\Services\Auth           auth()
 * @method \SoosyzeCore\FileSystem\Services\file     file()
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 * @method \SoosyzeCore\User\Services\User           user()
 *
 * @phpstan-import-type UserEntity from \SoosyzeCore\User\Extend
 */
class User extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function account(ServerRequestInterface $req): ResponseInterface
    {
        if ($user = self::user()->isConnected()) {
            return $this->show($user[ 'user_id' ], $req);
        }

        return new Response(403);
    }

    public function show(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }
        if (!$user[ 'actived' ] && !self::user()->isGranted('user.people.manage')) {
            return $this->get404($req);
        }

        $contentUser[] = self::user()->isConnected()
            ? self::template()
                ->getTheme('theme_admin')
                ->createBlock('user/content_user-roles.php', $this->pathViews)
                ->addVar('roles', self::user()->getRolesUser($id))
            : null;

        $this->container->callHook('user.show', [ &$contentUser, $user ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => $user[ 'username' ]
                ])
                ->view('page.submenu', self::user()->getUserSubmenu('user.show', $id))
                ->make('page.content', 'user/content-user-show.php', $this->pathViews, [
                    'content_user' => $contentUser,
                    'user'         => $user
                ]);
    }

    public function create(): ResponseInterface
    {
        $values = [];
        $this->container->callHook('user.create.form.data', [ &$values ]);

        $form = (new FormUser([
            'action'  => self::router()->generateUrl('user.store'),
            'enctype' => 'multipart/form-data',
            'method'  => 'post' ], self::file(), self::config()))
            ->setValues($values)
            ->informationsCreateFieldset()
            ->profilFieldset()
            ->passwordFieldset()
            ->activedFieldset()
            ->rolesFieldset($this->getRoleByPermission())
            ->submitForm('Save', true);

        $this->container->callHook('user.create.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('User creation')
                ])
                ->make('page.content', 'user/content-user-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function store(ServerRequestInterface $req): ResponseInterface
    {
        if ($req->isMaxSize()) {
            return $this->json(400, [
                    'messages'    => [
                        'errors' => [
                            t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
                        ]
                    ]
            ]);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('user.store.validator', [ &$validator ]);

        $rolesInput     = $validator->getInputArray('roles');
        $validatorRoles = $this->validRole($rolesInput);

        if ($validator->isValid() && $validatorRoles->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('user.store.before', [ $validator, &$data ]);
            self::query()->insertInto('user', array_keys($data))
                ->values($data)
                ->execute();

            /** @phpstan-var UserEntity $user */
            $user = self::user()->getUser($validator->getInputString('email'));

            self::query()
                ->insertInto('user_role', [ 'user_id', 'role_id' ])
                ->values([ $user[ 'user_id' ], 2 ]);

            foreach ($rolesInput as $role) {
                self::query()->values([ $user[ 'user_id' ], $role ]);
            }
            self::query()->execute();

            $this->savePicture($user[ 'user_id' ], $validator);
            $this->container->callHook('user.store.after', [ $validator, $data ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(201, [
                    'redirect' => self::router()->generateUrl('user.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function edit(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::user()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('user.edit.form.data', [ &$values, $id ]);

        $form = (new FormUser([
            'action'  => self::router()->generateUrl('user.update', [ 'id' => $id ]),
            'enctype' => 'multipart/form-data',
            'method'  => 'put' ], self::file(), self::config()))
            ->setValues($values)
            ->informationsFieldset()
            ->profilFieldset()
            ->passwordFieldset();

        if (self::user()->isGranted('user.permission.manage')) {
            $rolesUser = self::user()->getIdRolesUser($id);
            $form->activedFieldset();
        }

        $rolesUser = self::user()->getIdRolesUser($id);
        $form->setValues([ 'roles' => $rolesUser ])
            ->rolesFieldset($this->getRoleByPermission())
            ->submitForm();

        $this->container->callHook('user.edit.form', [ &$form, $values, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Editing a user')
                ])
                ->view('page.submenu', self::user()->getUserSubmenu('user.edit', $id))
                ->make('page.content', 'user/content-user-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function update(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($user = self::user()->find($id))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        if ($req->isMaxSize()) {
            return $this->json(400, [
                    'messages'    => [
                        'errors' => [
                            t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
                        ]
                    ]
            ]);
        }

        $validator = $this->getValidator($req, $user);

        $this->container->callHook('user.update.validator', [ &$validator, $id ]);

        $validatorRole = $this->validRole($validator->getInputArray('roles'));

        if ($validator->isValid() && $validatorRole->isValid()) {
            /* Prépare les donnée à mettre à jour. */
            $data = $this->getData($validator, $id);

            $this->container->callHook('user.update.before', [
                $validator, &$data, $id
            ]);
            self::query()->update('user', $data)->where('user_id', '=', $id)->execute();

            $this->updateRole($validator, $id);

            $this->savePicture($id, $validator);
            $this->container->callHook('user.update.after', [ &$validator, $data, $id ]);

            if (($userCurrent = self::user()->isConnected()) && $userCurrent[ 'user_id' ] == $id) {
                $pwd = empty($data[ 'password' ])
                    ? $validator->getInputString('password')
                    : $validator->getInputString('password_new');

                self::auth()->login($validator->getInputString('email'), $pwd);
            }
            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('user.edit', [ 'id' => $id ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() + $validatorRole->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function remove(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::user()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('user.remove.form.data', [ &$values, $id ]);

        $form = (new FormBuilder([
                'action' => self::router()->generateUrl('user.delete', [ 'id' => $id ]),
                'class' => 'form-api',
                'method' => 'delete'
                ]))
            ->group('user-fieldset', 'fieldset', function ($form) {
                $form->legend('user-legend', t('Account deletion'))
                ->group('info-group', 'div', function ($form) {
                    $form->html('info', '<p:attr>:content</p>', [
                        ':content' => t('Warning ! The deletion of the user account is final.')
                    ]);
                }, [ 'class' => 'alert alert-warning' ]);
            })
            ->group('submit-group', 'div', function ($form) {
                $form->token('token_user_remove')
                ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ])
                ->html('cancel', '<button:attr>:content</button>', [
                    ':content' => t('Cancel'),
                    'class'    => 'btn btn-default',
                    'onclick'  => 'javascript:history.back();',
                    'type'     => 'button'
                ]);
            });

        $this->container->callHook('user.remove.form', [ &$form, $values, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Delete :name account', [ ':name' => $values[ 'username' ] ])
                ])
                ->view('page.submenu', self::user()->getUserSubmenu('user.remove', $id))
                ->make('page.content', 'user/content-user-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function delete(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($user = self::user()->find($id))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->setRules([
                'id'                => 'required|int|!equal:1',
                'token_user_remove' => 'token'
            ])
            ->setInputs((array) $req->getParsedBody())
            ->addInput('id', $id)
            ->setMessages([
                'id' => [
                    'equal' => [
                        'not' => t('You cannot delete the site administrator account')
                    ]
                ]
            ]);

        $this->container->callHook('user.delete.validator', [
            &$validator, $user, $id
        ]);

        if ($validator->isValid()) {
            $this->container->callHook('user.delete.before', [
                $validator, $user, $id
            ]);

            self::query()->from('user_role')->where('user_id', '=', $id)->delete()->execute();
            self::query()->from('user')->where('user_id', '=', $id)->delete()->execute();

            $this->container->callHook('user.delete.after', [
                $validator, $user, $id
            ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('user.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function validRole(array $roles = []): Validator
    {
        $validatorRoles = new Validator();

        $listRoles = implode(
            ',',
            array_column($this->getRoleByPermission(), 'role_id')
        );

        foreach ($roles as $key => $role) {
            $validatorRoles
                ->addRule("$key-role", 'int|inarray:' . $listRoles)
                ->addLabel("$key-role", t($role))
                ->addInput("$key-role", $key);
        }

        $this->container->callHook('user.update.role.validator', [ &$validatorRoles ]);

        return $validatorRoles;
    }

    private function getRoleByPermission(): array
    {
        $roles = self::user()->getRolesAttribuable();

        if (!$this->container->callHook('app.granted', [ 'role.all' ])) {
            foreach ($roles as $key => $role) {
                if (!$this->container->callHook('app.granted', [ 'role.' . $role[ 'role_id' ] ])) {
                    unset($roles[ $key ]);
                }
            }
        }

        return $roles;
    }

    private function getValidator(ServerRequestInterface $req, ?array $user = null): Validator
    {
        $passwordPolicy = self::user()->passwordPolicy();

        $validator = (new Validator())
                ->setInputs((array) $req->getParsedBody() + $req->getUploadedFiles())
                ->setRules([
                    'actived'          => 'bool',
                    'bio'              => '!required|string|max:255',
                    /* max:254 RFC5321 - 4.5.3.1.3. */
                    'email'            => 'required|email|max:254|!equal:@is_email',
                    'firstname'        => '!required|string|max:255',
                    'name'             => '!required|string|max:255',
                    'password_confirm' => 'required_with:password_new|string|equal:@password_new',
                    'password_new'     => '!required|string|regex:' . $passwordPolicy,
                    'picture'          => '!required|image:jpeg,jpg,png|max:200Kb',
                    'roles'            => '!required|array',
                    'token_user_form'  => 'required|token',
                    'username'         => 'required|string|max:255|!equal:@is_username'
                ])
                ->setLabels([
                    'actived'          => t('Active'),
                    'bio'              => t('Biography'),
                    'email'            => t('E-mail'),
                    'firstname'        => t('First name'),
                    'name'             => t('Name'),
                    'password'         => t('Password'),
                    'password_confirm' => t('Confirmation of the new password'),
                    'password_new'     => t('New Password'),
                    'picture'          => t('Picture'),
                    'roles'            => t('User Roles'),
                    'username'         => t('User name')
                ])
                ->setMessages([
                    'email'            => [ 'equal' => [ 'not' => t('The :value email is unavailable.') ] ],
                    'password'         => [ 'required' => [ 'must' => t(':label is incorrect') ] ],
                    'password_confirm' => [ 'equal' => [ 'must' => t(':label is incorrect') ] ],
                    'username'         => [ 'equal' => [ 'not' => t('The :value username is unavailable.') ] ],
                ]);

        if ($user === null) {
            $validator->addRule('password_new', 'required|string|regex:' . $passwordPolicy);
            /* La vérification doit être faite à la création de l'utilisateur. */
            $isUpdateUsername = true;
            $isUpdateEmail    = true;
        } else {
            /* La vérification doit être faite si l'utilisateur met à jour ses informations. */
            $isUpdateUsername = $validator->getInput('username') !== $user[ 'username' ];
            $isUpdateEmail    = $validator->getInput('email') !== $user[ 'email' ];

            if ($isUpdateEmail || $isUpdateUsername) {
                $validator->addRule('password', 'required|string');

                if (!self::auth()->hashVerify($validator->getInputString('password'), $user)) {
                    $validator->addInput('password', '');
                }
            }
        }

        $isUsername = $isUpdateUsername && ($userName = self::user()->getUserByUsername($validator->getInputString('username')))
            ? $userName[ 'username' ]
            : '';

        $isEmail   = $isUpdateEmail && ($userEmail = self::user()->getUser($validator->getInputString('email')))
            ? $userEmail[ 'email' ]
            : '';

        $validator
            ->addInput('is_email', $isEmail)
            ->addInput('is_username', $isUsername);

        return $validator;
    }

    private function getData(Validator $validator, ?int $id = null): array
    {
        /* Prépare les donnée à mettre à jour. */
        $data = [
            'bio'       => $validator->getInput('bio'),
            'email'     => $validator->getInput('email'),
            'firstname' => $validator->getInput('firstname'),
            'name'      => $validator->getInput('name'),
            'username'  => $validator->getInput('username'),
        ];

        if ($id === null) {
            $data[ 'password' ]       = self::auth()->hash($validator->getInputString('password_new'));
            $data[ 'time_installed' ] = (string) time();
            $data[ 'timezone' ]       = 'Europe/Paris';
        } elseif ($validator->getInput('password_new') !== '') {
            $data[ 'password' ] = self::auth()->hash($validator->getInputString('password_new'));
        }

        /* Si l'utilisateur à les droits d'administrer les autres utilisateurs. */
        if (self::user()->isGranted('user.people.manage')) {
            $data[ 'actived' ] = (bool) $validator->getInput('actived');
        }

        return $data;
    }

    private function updateRole(Validator $validator, int $idUser): void
    {
        $this->container->callHook('user.update.role.before', [ &$validator, $idUser ]);

        $listRoles = array_column($this->getRoleByPermission(), 'role_id');

        self::query()
            ->from('user_role')
            ->where('user_id', '=', $idUser)
            ->in('role_id', $listRoles)
            ->delete()
            ->execute();

        self::query()->insertInto('user_role', [ 'user_id', 'role_id' ]);

        foreach (array_keys($validator->getInputArray('roles')) as $idRole) {
            self::query()->values([ $idUser, $idRole ]);
        }

        self::query()->execute();

        $this->container->callHook('user.update.role.after', [ $validator, $idUser ]);
    }

    private function savePicture(int $id, Validator $validator): void
    {
        $key = 'picture';

        /** @phpstan-var UploadedFileInterface $uploadedFile */
        $uploadedFile = $validator->getInput($key);

        self::file()
            ->add($uploadedFile, $validator->getInputString("file-$key-name"))
            ->setName($key)
            ->setPath("/user/$id")
            ->isResolvePath()
            ->callGet(function (string $key, string $name) use ($id): ?string {
                $user = self::user()->find($id);

                return $user[ $key ] ?? null;
            })
            ->callMove(function (string $key, string $name, string $move) use ($id): void {
                self::query()->update('user', [ $key => $move ])->where('user_id', '=', $id)->execute();
            })
            ->callDelete(function (string $key, string $name) use ($id): void {
                self::query()->update('user', [ $key => '' ])->where('user_id', '=', $id)->execute();
            })
            ->save();
    }
}
