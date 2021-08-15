<?php

declare(strict_types=1);

namespace SoosyzeCore\User\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUserRole;

class Role extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create(ServerRequestInterface $req): ResponseInterface
    {
        $values = [];
        $this->container->callHook('role.create.form.data', [ &$values ]);

        $form = (new FormUserRole([
            'action' => self::router()->getRoute('user.role.store'),
            'method' => 'post'
            ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('role.create.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user-tag" aria-hidden="true"></i>',
                    'title_main' => t('Creating a role')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'user/content-role-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store(ServerRequestInterface $req): ResponseInterface
    {
        $validator = $this->getValidator($req);

        $this->container->callHook('role.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('role.store.before', [ &$validator, &$data ]);
            self::query()->insertInto('role', array_keys($data))->values($data)->execute();
            $this->container->callHook('role.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return $this->json(201, [
                    'redirect' => self::router()->getRoute('user.role.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function edit(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = $this->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('role.edit.form.data', [ &$values, $id ]);

        $form = (new FormUserRole([
            'action' => self::router()->getRoute('user.role.update', [ ':id' => $id ]),
            'method' => 'post'
            ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('role.edit.form', [ &$form, $values, $id ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user-tag" aria-hidden="true"></i>',
                    'title_main' => t('Editing a role')
                ])
                ->view('page.messages', $messages)
                ->view('page.submenu', $this->getRoleSubmenu('user.role.edit', $id))
                ->make('page.content', 'user/content-role-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function update(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!$this->find($id)) {
            return $this->get404($req);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('role.update.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('role.udpate.before', [
                &$validator, &$data, $id
            ]);
            self::query()->update('role', $data)->where('role_id', '=', $id)->execute();
            $this->container->callHook('role.udpate.after', [
                $validator, $data, $id
            ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('user.role.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function remove(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($data = $this->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('role.remove.form.data', [ &$data, $id ]);

        $form = (new FormUserRole([
            'action' => self::router()->getRoute('user.role.delete', [ ':id' => $id ]),
            'method' => 'post'
            ]))
            ->makeFieldsDelete();

        $this->container->callHook('role.remove.form', [ &$form, $data, $id ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user-tag" aria-hidden="true"></i>',
                    'title_main' => t('Remove :name role', [ ':name' => $data[ 'role_label' ] ])
                ])
                ->view('page.messages', $messages)
                ->view('page.submenu', $this->getRoleSubmenu('user.role.remove', $id))
                ->make('page.content', 'user/content-role-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function delete(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!$this->find($id)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'id'                => 'required|int|!inarray:1,2,3',
                'token_role_delete' => 'required|token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('id', $id);

        $this->container->callHook('role.delete.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('role.delete.before', [ $validator, $id ]);

            self::query()->from('user_role')
                ->where('role_id', '=', $id)
                ->delete()
                ->execute();
            self::query()
                ->from('role_permission')
                ->where('role_id', '=', $id)
                ->delete()
                ->execute();
            self::query()
                ->from('role')
                ->where('role_id', '=', $id)
                ->delete()
                ->execute();

            $this->container->callHook('role.delete.after', [ $validator, $id ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('user.role.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function getValidator(ServerRequestInterface $req): Validator
    {
        return (new Validator())
                ->setRules([
                    'role_description'  => '!required|string|max:255',
                    'role_label'        => 'required|string|max:255',
                    'role_color'        => '!required|colorhex',
                    'role_icon'         => '!required|max:255|fontawesome:solid,brands',
                    'role_weight'       => '!required|between_numeric:1,50',
                    'token_role_submit' => 'required|token'
                ])
                ->setLabels([
                    'role_description' => t('Description'),
                    'role_label'       => t('Name'),
                    'role_color'       => t('Color'),
                    'role_icon'        => t('Icon'),
                    'role_weight'      => t('Weight')
                ])
                ->setInputs($req->getParsedBody());
    }

    private function getData(Validator $validator): array
    {
        $roleWeight = $validator->getInput('role_weight');
        $roleColor  = $validator->getInput('role_color');
        $roleIcon   = $validator->getInput('role_icon');

        return [
            'role_description' => $validator->getInput('role_description'),
            'role_label'       => $validator->getInput('role_label'),
            'role_color'       => empty($roleColor)
                ? '#e6e7f4'
                : strtolower($roleColor),
            'role_icon'        => empty($roleIcon)
                ? 'fa fa-user'
                : strtolower($roleIcon),
            'role_weight'      => empty($roleWeight)
                ? 1
                : (int) $roleWeight,
        ];
    }

    private function find(int $id): array
    {
        return self::query()->from('role')->where('role_id', '=', $id)->fetch();
    }

    private function getRoleSubmenu(string $keyRoute, int $idRole): array
    {
        $menu = [
            [
                'key'        => 'user.role.edit',
                'request'    => self::router()->getRequestByRoute('user.role.edit', [
                    ':id' => $idRole
                ]),
                'title_link' => t('Edit')
            ], [
                'key'        => 'user.role.remove',
                'request'    => self::router()->getRequestByRoute('user.role.remove', [
                    ':id' => $idRole
                ]),
                'title_link' => t('Delete')
            ]
        ];

        $this->container->callHook('user.role.submenu', [ &$menu ]);

        foreach ($menu as $key => &$link) {
            if (!self::user()->isGrantedRequest($link[ 'request' ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = $link[ 'request' ]->getUri();
        }
        unset($link);

        return [
            'key_route' => $keyRoute,
            'menu'      => count($menu) === 1
                ? []
                : $menu
        ];
    }
}
