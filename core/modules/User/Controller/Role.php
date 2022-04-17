<?php

declare(strict_types=1);

namespace SoosyzeCore\User\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUserRole;

/**
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 * @method \SoosyzeCore\User\Services\User           user()
 *
 * @phpstan-import-type RoleEntity from \SoosyzeCore\User\Extend
 * @phpstan-type Submenu array<
 *      array{
 *          key: string,
 *          link?: string,
 *          request: \Psr\Http\Message\RequestInterface,
 *          title_link: string
 *      }
 *  >
 */
class Role extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create(ServerRequestInterface $req): ResponseInterface
    {
        $values = [];
        $this->container->callHook('user.role.create.form.data', [ &$values ]);

        $form = (new FormUserRole([
            'action' => self::router()->generateUrl('user.role.store'),
            'method' => 'post'
            ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('user.role.create.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user-tag" aria-hidden="true"></i>',
                    'title_main' => t('Creating a role')
                ])
                ->make('page.content', 'user/content-role-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store(ServerRequestInterface $req): ResponseInterface
    {
        $validator = $this->getValidator($req);

        $this->container->callHook('user.role.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('user.role.store.before', [ $validator, &$data ]);
            self::query()->insertInto('role', array_keys($data))->values($data)->execute();
            $this->container->callHook('user.role.store.after', [ $validator, $data ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(201, [
                    'redirect' => self::router()->generateUrl('user.role.admin')
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

        $this->container->callHook('user.role.edit.form.data', [ &$values, $id ]);

        $form = (new FormUserRole([
            'action' => self::router()->generateUrl('user.role.update', [ 'id' => $id ]),
            'method' => 'put'
            ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('user.role.edit.form', [ &$form, $values, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user-tag" aria-hidden="true"></i>',
                    'title_main' => t('Editing a role')
                ])
                ->view('page.submenu', $this->getRoleSubmenu('user.role.edit', $id))
                ->make('page.content', 'user/content-role-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function update(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!$this->find($id)) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('user.role.update.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('user.role.udpate.before', [
                $validator, &$data, $id
            ]);
            self::query()->update('role', $data)->where('role_id', '=', $id)->execute();
            $this->container->callHook('user.role.udpate.after', [
                $validator, $data, $id
            ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('user.role.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function remove(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = $this->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('user.role.remove.form.data', [ &$values, $id ]);

        $form = (new FormUserRole([
            'action' => self::router()->generateUrl('user.role.delete', [ 'id' => $id ]),
            'method' => 'delete'
            ]))
            ->makeFieldsDelete();

        $this->container->callHook('user.role.remove.form', [ &$form, $values, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user-tag" aria-hidden="true"></i>',
                    'title_main' => t('Remove :name role', [ ':name' => $values[ 'role_label' ] ])
                ])
                ->view('page.submenu', $this->getRoleSubmenu('user.role.remove', $id))
                ->make('page.content', 'user/content-role-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function delete(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!$this->find($id)) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->setRules([
                'id'                => 'required|int|!inarray:1,2,3',
                'token_role_delete' => 'required|token'
            ])
            ->setInputs((array) $req->getParsedBody())
            ->addInput('id', $id);

        $this->container->callHook('user.role.delete.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('user.role.delete.before', [ $validator, $id ]);

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

            $this->container->callHook('user.role.delete.after', [ $validator, $id ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('user.role.admin')
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
                ->setInputs((array) $req->getParsedBody());
    }

    private function getData(Validator $validator): array
    {
        $roleColor  = $validator->getInputString('role_color');
        $roleIcon   = $validator->getInputString('role_icon');

        return [
            'role_description' => $validator->getInput('role_description'),
            'role_label'       => $validator->getInput('role_label'),
            'role_color'       => empty($roleColor)
                ? '#e6e7f4'
                : strtolower($roleColor),
            'role_icon'        => empty($roleIcon)
                ? 'fa fa-user'
                : strtolower($roleIcon),
            'role_weight'      => $validator->getInputInt('role_weight', 1),
        ];
    }

    private function find(int $id): ?array
    {
        return self::query()->from('role')->where('role_id', '=', $id)->fetch();
    }

    private function getRoleSubmenu(string $keyRoute, int $idRole): array
    {
        /** @phpstan-var Submenu $menu */
        $menu = [
            [
                'key'        => 'user.role.edit',
                'request'    => self::router()->generateRequest('user.role.edit', [
                    'id' => $idRole
                ]),
                'title_link' => t('Edit')
            ], [
                'key'        => 'user.role.remove',
                'request'    => self::router()->generateRequest('user.role.remove', [
                    'id' => $idRole
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
