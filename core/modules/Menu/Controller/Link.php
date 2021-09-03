<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Form\FormLink;

class Link extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create(string $nameMenu, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($nameMenu)->fetch()) {
            return $this->get404($req);
        }
        $values = [];
        $this->container->callHook('menu.link.create.form.data', [ &$values ]);

        $action = self::router()->getRoute('menu.link.store', [ ':menu' => $nameMenu ]);

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

    public function store(string $nameMenu, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($nameMenu)->fetch()) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.link.store.validator', [ &$validator ]);

        $infoUrlOrRoute = self::menu()->getInfo($validator->getInput('link'), $req);

        if ($validator->isValid()) {
            $data = $this->getData($validator, $nameMenu, $infoUrlOrRoute);

            $this->container->callHook('menu.link.store.before', [ $validator, &$data ]);
            self::query()
                ->insertInto('menu_link', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.link.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(201, [
                    'redirect' => self::router()->getRoute('menu.show', [
                        ':menu' => $nameMenu
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function edit(string $nameMenu, int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::menu()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.link.edit.form.data', [ &$values ]);

        $action = self::router()->getRoute('menu.link.update', [
            ':menu' => $nameMenu, ':id' => $id
        ]);

        $form = (new FormLink([ 'action' => $action, 'method' => 'put' ], self::router()))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.link.edit.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-link" aria-hidden="true"></i>',
                    'title_main' => t('Edit a link')
                ])
                ->view('page.submenu', self::menu()->getMenuLinkSubmenu('menu.link.edit', $values[ 'menu' ], $id))
                ->make('page.content', 'menu/content-link-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function update(string $nameMenu, int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->find($id)) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.link.update.validator', [ &$validator ]);

        $infoUrlOrRoute = self::menu()->getInfo($validator->getInput('link'), $req);

        if ($validator->isValid()) {
            $data = $this->getData($validator, $nameMenu, $infoUrlOrRoute, $id);

            $this->container->callHook('menu.link.update.before', [ $validator, &$data ]);
            self::query()
                ->update('menu_link', $data)
                ->where('id', '=', $id)
                ->execute();
            $this->container->callHook('menu.link.update.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('menu.show', [
                        ':menu' => $nameMenu
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function remove(string $nameMenu, int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::menu()->find($id))) {
            return $this->get404($req);
        }

        $form = $this->formDelete($values)
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
                ->view('page.submenu', self::menu()->getMenuLinkSubmenu('menu.link.remove', $values[ 'menu' ], $id))
                ->make('page.content', 'menu/content-link-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    /**
     * @return ServiceBlock|ResponseInterface
     */
    public function removeModal(string $nameMenu, int $id, ServerRequestInterface $req)
    {
        if (!($values = self::menu()->find($id))) {
            return $this->get404($req);
        }

        $form = $this->formDelete($values);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('menu/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'title' => t('Delete a link')
        ]);
    }

    public function delete(string $nameMenu, int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($linkMenu = self::menu()->find($id))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->setRules([
                'id'   => 'required|int',
                'name' => 'required|string|max:255'
            ])
            ->setInputs([ 'name' => $nameMenu, 'id' => $id ]);

        $this->container->callHook('menu.link.delete.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('menu.link.delete.before', [ $validator, $id ]);

            self::menu()->deleteLinks(static function () use ($linkMenu) {
                return [ $linkMenu ];
            });

            $this->container->callHook('menu.link.delete.after', [ $validator, $id ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('menu.show', [ ':menu' => $nameMenu ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function formDelete(array $values): FormBuilder
    {
        $this->container->callHook('menu.link.remove.form.data', [ &$values ]);

        $action = self::router()->getRoute('menu.link.delete', [
            ':menu' => $values['menu'], ':id'   => $values['id']
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
            ->token('token_menu_remove')
            ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ]);

        $this->container->callHook('menu.link.remove.form', [ &$form, $values ]);

        return $form;
    }

    private function getValidator(ServerRequestInterface $req): Validator
    {
        return (new Validator())
                ->setRules([
                    'icon'            => '!required|max:255|fontawesome:solid,brands',
                    'link'            => 'required|route_or_url',
                    'target_link'     => 'bool',
                    'title_link'      => 'required|string|max:255',
                    'token_link_form' => 'required|token'
                ])
                ->setLabels([
                    'icon'        => t('Icon'),
                    'link'        => t('Link'),
                    'target_link' => t('Target'),
                    'title_link'  => t('Link title')
                ])
                ->setInputs($req->getParsedBody());
    }

    private function getData(
        Validator $validator,
        string $nameMenu,
        array $infoUrlOrRoute,
        ?int $id = null
    ): array {
        $data = [
            'fragment'    => $infoUrlOrRoute[ 'fragment' ],
            'icon'        => $validator->getInput('icon'),
            'key'         => $infoUrlOrRoute[ 'key' ],
            'link'        => $infoUrlOrRoute[ 'link' ],
            'link_router' => $infoUrlOrRoute[ 'link_router' ],
            'query'       => $infoUrlOrRoute[ 'query' ],
            'target_link' => (bool) $validator->getInput('target_link'),
            'title_link'  => $validator->getInput('title_link')
        ];

        if ($id === null) {
            $data += [
                'active' => true,
                'menu'   => $nameMenu,
                'parent' => -1,
                'weight' => 1
            ];
        }

        return $data;
    }
}
