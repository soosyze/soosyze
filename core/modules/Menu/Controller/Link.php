<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Form\FormLink;
use SoosyzeCore\Template\Services\Block;

/**
 * @method \SoosyzeCore\Menu\Services\Menu           menu()
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 */
class Link extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create(int $menuId, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($menuId)->fetch()) {
            return $this->get404($req);
        }
        $values = [ 'menu_id' => $menuId ];
        $this->container->callHook('menu.link.create.form.data', [ &$values ]);

        $action = self::router()->generateUrl('menu.link.store', [ 'menuId' => $menuId ]);

        $form = (new FormLink([ 'action' => $action, 'method' => 'post' ], self::router()))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.link.create.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-link" aria-hidden="true"></i>',
                    'title_main' => t('Add a link')
                ])
                ->make('page.content', 'menu/content-link-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store(int $menuId, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($menuId)->fetch()) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.link.store.validator', [ &$validator, $menuId ]);

        $infoUrlOrRoute = self::menu()->getInfo($validator->getInputString('link'), $req);

        if ($validator->isValid()) {
            $data = $this->getData($validator, $infoUrlOrRoute);

            $this->container->callHook('menu.link.store.before', [ $validator, &$data, $menuId ]);
            self::query()
                ->insertInto('menu_link', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.link.store.after', [ $validator, $menuId ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(201, [
                    'redirect' => self::router()->generateUrl('menu.show', [
                        'menuId' => $menuId
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function edit(int $menuId, int $linkId, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::menu()->find($linkId))) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.link.edit.form.data', [
            &$values, $menuId, $linkId
        ]);

        $action = self::router()->generateUrl('menu.link.update', [
            'menuId' => $menuId,
            'linkId' => $linkId
        ]);

        $form = (new FormLink([ 'action' => $action, 'method' => 'put' ], self::router()))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.link.edit.form', [
            &$form, $values, $menuId, $linkId
        ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-link" aria-hidden="true"></i>',
                    'title_main' => t('Edit a link')
                ])
                ->view('page.submenu', self::menu()->getMenuLinkSubmenu('menu.link.edit', $values[ 'menu_id' ], $linkId))
                ->make('page.content', 'menu/content-link-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function update(int $menuId, int $linkId, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->find($linkId)) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.link.update.validator', [
            &$validator, $menuId, $linkId
        ]);

        $infoUrlOrRoute = self::menu()->getInfo($validator->getInputString('link'), $req);

        if ($validator->isValid()) {
            $data = $this->getData($validator, $infoUrlOrRoute, $linkId);

            $this->container->callHook('menu.link.update.before', [
                $validator, &$data, $menuId, $linkId
            ]);
            self::query()
                ->update('menu_link', $data)
                ->where('link_id', '=', $linkId)
                ->execute();
            $this->container->callHook('menu.link.update.after', [
                $validator, $data, $menuId, $linkId
            ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('menu.show', [
                        'menuId' => $menuId
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function remove(int $menuId, int $linkId, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::menu()->find($linkId))) {
            return $this->get404($req);
        }

        $form = $this->formRemove($values, $menuId, $linkId)
            ->html('cancel', '<button:attr>:content</button>', [
            ':content' => t('Cancel'),
            'class'    => 'btn btn-default',
            'onclick'  => 'javascript:history.back();',
            'type'     => 'button'
        ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-link" aria-hidden="true"></i>',
                    'title_main' => t('Delete a link')
                ])
                ->view('page.submenu', self::menu()->getMenuLinkSubmenu('menu.link.remove', $values[ 'menu' ], $linkId))
                ->make('page.content', 'menu/content-link-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    /**
     * @return Block|ResponseInterface
     */
    public function removeModal(int $menuId, int $linkId, ServerRequestInterface $req)
    {
        if (!($values = self::menu()->find($linkId))) {
            return $this->get404($req);
        }

        $form = $this->formRemove($values, $menuId, $linkId);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('menu/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'title' => t('Delete a link')
        ]);
    }

    public function delete(int $menuId, int $linkId, ServerRequestInterface $req): ResponseInterface
    {
        if (!($linkMenu = self::menu()->find($linkId))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->addRule('token_menu_remove', 'token')
            ->setInputs((array) $req->getParsedBody());

        $this->container->callHook('menu.link.delete.validator', [
            &$validator, $menuId, $linkId
        ]);

        if ($validator->isValid()) {
            $this->container->callHook('menu.link.delete.before', [
                $validator, $menuId, $linkId
            ]);

            self::menu()->deleteLinks(static function () use ($linkMenu): array {
                return [ $linkMenu ];
            });

            $this->container->callHook('menu.link.delete.after', [
                $validator, $menuId, $linkId
            ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('menu.show', [ 'menuId' => $menuId ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function formRemove(array $values, int $menuId, int $linkId): FormBuilder
    {
        $this->container->callHook('menu.link.remove.form.data', [
            &$values, $menuId, $linkId
        ]);

        $action = self::router()->generateUrl('menu.link.delete', [
            'menuId' => $menuId,
            'linkId' => $linkId
        ]);

        $form = (new FormBuilder([ 'action' => $action, 'class' => 'form-api', 'method' => 'delete' ]))
            ->group('link-fieldset', 'fieldset', function ($form) {
                $form->legend('link-legend', t('Link deletion'))
                ->group('info-group', 'div', function ($form) {
                    $form->html('info', '<p:attr>:content</p>', [
                        ':content' => t('Warning ! The deletion of the link is final.')
                    ]);
                }, [ 'class' => 'alert alert-warning' ]);
            })
            ->group('submit-group', 'div', function ($form) {
                $form->token('token_menu_remove')
                ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ]);
            });

        $this->container->callHook('menu.link.remove.form', [
            &$form, $values, $menuId, $linkId
        ]);

        return $form;
    }

    private function getValidator(ServerRequestInterface $req): Validator
    {
        $menus = self::menu()->getAllMenu();

        return (new Validator())
                ->setRules([
                    'active'          => 'bool',
                    'icon'            => '!required|max:255|fontawesome:solid,brands',
                    'link'            => 'required|route_or_url',
                    'menu_id'         => 'required|int|inarray:' . implode(',', array_column($menus, 'menu_id')),
                    'target_link'     => 'bool',
                    'title_link'      => 'required|string|max:255',
                    'token_link_form' => 'required|token',
                    'weight'          => '!required|numeric|between_numeric:0,50'
                ])
                ->setLabels([
                    'active'      => t('Active'),
                    'icon'        => t('Icon'),
                    'link'        => t('Link'),
                    'menu_id'     => t('Menu'),
                    'target_link' => t('Target'),
                    'title_link'  => t('Link title'),
                    'weight'      => t('Weight')
                ])
                ->setInputs((array) $req->getParsedBody())
                ->setAttributs([
                    'menu_id' => [
                        'inarray' => [
                            ':list' => static function () use ($menus): string {
                                return implode(', ', array_column($menus, 'title'));
                            }
                        ]
                    ],
                ])
        ;
    }

    private function getData(
        Validator $validator,
        array $infoUrlOrRoute,
        ?int $linkId = null
    ): array {
        $data = [
            'active'      => (bool) $validator->getInput('active'),
            'fragment'    => $infoUrlOrRoute[ 'fragment' ],
            'icon'        => $validator->getInput('icon'),
            'key'         => $infoUrlOrRoute[ 'key' ],
            'link'        => $infoUrlOrRoute[ 'link' ],
            'link_router' => $infoUrlOrRoute[ 'link_router' ],
            'menu_id'     => $validator->getInputInt('menu_id'),
            'query'       => $infoUrlOrRoute[ 'query' ],
            'target_link' => (bool) $validator->getInput('target_link'),
            'title_link'  => $validator->getInput('title_link'),
            'weight'      => $validator->getInputInt('weight')
        ];

        if ($linkId === null) {
            $data += [
                'parent' => -1,
            ];
        }

        return $data;
    }
}
