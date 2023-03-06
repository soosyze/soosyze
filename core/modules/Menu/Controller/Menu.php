<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Form\FormMenu;
use SoosyzeCore\Template\Services\Block;

class Menu extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin(ServerRequestInterface $req): ResponseInterface
    {
        return $this->show('menu-main', $req);
    }

    public function show(string $name, ServerRequestInterface $req): ResponseInterface
    {
        if (!($menu = self::menu()->getMenu($name)->fetch())) {
            return $this->get404($req);
        }

        $action = self::router()->getRoute('menu.check', [ ':menu' => $name ]);

        $form = (new FormBuilder([ 'action' => $action, 'class' => 'form-api', 'method' => 'patch' ]))
            ->token('token_menu')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t($menu[ 'title' ])
                ])
                ->view('page.messages', $messages)
                ->view('page.submenu', $this->getMenuSubmenu('menu.show', $menu[ 'name' ]))
                ->make('page.content', 'menu/content-menu-show.php', $this->pathViews, [
                    'form'              => $form,
                    'link_create_link'  => self::router()->getRoute('menu.link.create', [
                        ':menu' => $name
                    ]),
                    'link_create_menu'  => self::router()->getRoute('menu.create'),
                    'list_menu_submenu' => $this->getListMenuSubmenu($name),
                    'menu'              => $this->renderMenu($name),
                    'menu_name'         => $menu[ 'title' ]
        ]);
    }

    public function check(string $name, ServerRequestInterface $req): ResponseInterface
    {
        $route = self::router()->getRoute('menu.show', [ ':menu' => $name ]);
        if (!($links = self::menu()->getLinkPerMenu($name)->fetchAll())) {
            return $this->json(200, [ 'redirect' => $route ]);
        }

        $validator = new Validator();
        foreach ($links as $link) {
            $validator
                ->addRule("active-{$link[ 'id' ]}", 'bool')
                ->addRule("parent-{$link[ 'id' ]}", 'required|numeric')
                ->addRule("weight-{$link[ 'id' ]}", 'required|between_numeric:1,50');
        }
        $validator->addRule('token_menu', 'token')
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            $updateParents = [];
            foreach ($links as $link) {
                $data = [
                    'active'       => $validator->getInput("active-{$link[ 'id' ]}") === 'on',
                    'has_children' => false,
                    'parent'       => (int) $validator->getInput("parent-{$link[ 'id' ]}"),
                    'weight'       => (int) $validator->getInput("weight-{$link[ 'id' ]}")
                ];

                self::query()
                    ->update('menu_link', $data)
                    ->where('id', '=', $link[ 'id' ])
                    ->execute();

                if ($data[ 'parent' ] >= 1 && !in_array($data[ 'parent' ], $updateParents)) {
                    $updateParents[] = $data[ 'parent' ];
                }
            }
            /* Mise à jour des parents. */
            foreach ($updateParents as $parent) {
                self::query()
                    ->update('menu_link', [ 'has_children' => true ])
                    ->where('id', '=', $parent)
                    ->execute();
            }

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return $this->json(200, [ 'redirect' => $route ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function create(ServerRequestInterface $req): ResponseInterface
    {
        $values = [];
        $this->container->callHook('menu.create.form.data', [ &$values ]);

        $action = self::router()->getRoute('menu.store');

        $form = (new FormMenu([ 'action' => $action, 'method' => 'post' ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.create.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Add a menu')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu/content-menu-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store(ServerRequestInterface $req): ResponseInterface
    {
        $validator = $this->getValidator($req);

        $this->container->callHook('menu.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('menu.store.before', [ $validator, &$data ]);
            self::query()
                ->insertInto('menu', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return $this->json(201, [
                    'redirect' => self::router()->getRoute('menu.show', [ ':menu' => $data[ 'name' ] ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function edit(string $menu, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::menu()->getMenu($menu)->fetch())) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.store.form.data', [ &$values ]);

        $action = self::router()->getRoute('menu.update', [ ':menu' => $menu ]);

        $form = (new FormMenu(['action' => $action, 'method' => 'put' ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.store.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Edit the menu :name', [
                        ':name' => t($values[ 'title' ])
                    ])
                ])
                ->view('page.messages', $messages)
                ->view('page.submenu', $this->getMenuSubmenu('menu.edit', $menu))
                ->make('page.content', 'menu/content-menu-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function update(string $menu, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($menu)->fetch()) {
            return $this->get404($req);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.update.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator, $menu);

            $this->container->callHook('menu.update.before', [ $validator, &$data ]);
            self::query()
                ->update('menu', $data)
                ->where('name', '=', $menu)
                ->execute();
            $this->container->callHook('menu.update.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('menu.show', [ ':menu' => $menu ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function remove(string $name, ServerRequestInterface $req): ResponseInterface
    {
        if (!($menu = self::menu()->getMenu($name)->fetch())) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.remove.form.data', [ &$menu, $name ]);

        $action = self::router()->getRoute('menu.delete', [ ':menu' => $name ]);

        $form = (new FormBuilder([ 'action' => $action, 'class' => 'form-api', 'method' => 'delete' ]))
            ->group('menu-fieldset', 'fieldset', function ($form) {
                $form->legend('menu-legend', t('Menu deletion'))
                ->group('info-group', 'div', function ($form) {
                    $form->html('info', '<p:attr>:content</p>', [
                        ':content' => t('Warning ! The deletion of the menu is final.')
                    ]);
                }, [ 'class' => 'alert alert-warning' ]);
            })
            ->token('token_menu_remove')
            ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ])
            ->html('cancel', '<button:attr>:content</button>', [
                ':content' => t('Cancel'),
                'class'    => 'btn btn-default',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ]);

        $this->container->callHook('menu.remove.form', [ &$form, $menu, $name ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Delete the menu :name', [
                        ':name' => t($menu[ 'title' ])
                    ])
                ])
                ->view('page.submenu', $this->getMenuSubmenu('menu.remove', $name))
                ->make('page.content', 'menu/content-menu-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function delete(string $menu, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($menu)->fetch()) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'name' => 'required|string|max:255',
            ])
            ->setInputs([ 'name' => $menu ]);

        $this->container->callHook('menu.delete.validator', [ &$validator, $menu ]);

        if ($validator->isValid()) {
            $this->container->callHook('menu.delete.before', [ $validator, $menu ]);
            if (self::module()->has('Block')) {
                self::query()
                    ->from('block')
                    ->delete()
                    ->where('key_block', 'like', 'menu.' . $menu)
                    ->execute();
            }
            self::query()
                ->from('menu_link')
                ->delete()
                ->where('menu', '=', $menu)
                ->execute();
            self::query()
                ->from('menu')
                ->delete()
                ->where('name', '=', $menu)
                ->execute();
            $this->container->callHook('menu.delete.after', [ $validator, $menu ]);

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('menu.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function renderMenu(string $nameMenu, int $parent = -1, int $level = 1): Block
    {
        $query = self::query()
            ->from('menu_link')
            ->where('menu', '=', $nameMenu)
            ->where('parent', '=', $parent)
            ->orderBy('weight')
            ->fetchAll();

        foreach ($query as &$link) {
            $link[ 'link_edit' ]   = self::router()
                ->getRoute('menu.link.edit', [ ':menu' => $link[ 'menu' ], ':id' => $link[ 'id' ] ]);
            $link[ 'link_delete' ] = self::router()
                ->getRoute('menu.link.delete', [ ':menu' => $link[ 'menu' ], ':id' => $link[ 'id' ] ]);
            $link[ 'submenu' ]     = $link[ 'has_children' ]
                ? $this->renderMenu($nameMenu, $link[ 'id' ], $level + 1)
                : $this->createBlockMenuShowForm($nameMenu, null, $level + 1);

            if (!$link[ 'key' ]) {
                continue;
            }

            $link[ 'link' ] = self::menu()->rewiteUri($link['link'], $link['query'], $link['fragment']);
        }
        unset($link);

        return $this->createBlockMenuShowForm($nameMenu, $query, $level);
    }

    public function getMenuSubmenu(string $keyRoute, string $nameMenu): array
    {
        $menu = [
            [
                'key'        => 'menu.show',
                'request'    => self::router()->getRequestByRoute('menu.show', [
                    ':menu' => $nameMenu
                ]),
                'title_link' => 'View'
            ], [
                'key'        => 'menu.edit',
                'request'    => self::router()->getRequestByRoute('menu.edit', [
                    ':menu' => $nameMenu
                ]),
                'title_link' => 'Edit'
            ], [
                'key'        => 'menu.remove',
                'request'    => self::router()->getRequestByRoute('menu.remove', [
                    ':menu' => $nameMenu
                ]),
                'title_link' => 'Delete'
            ]
        ];

        $this->container->callHook('menu.submenu', [ &$menu ]);

        foreach ($menu as $key => &$link) {
            if (!self::core()->callHook('app.granted.request', [ $link[ 'request' ] ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = $link[ 'request' ]->getUri();
        }

        return [
            'key_route' => $keyRoute,
            'menu'      => $menu
        ];
    }

    public function getListMenuSubmenu(string $nameMenu): Block
    {
        $menus = self::query()
            ->from('menu')
            ->fetchAll();

        foreach ($menus as &$menu) {
            $menu[ 'link' ] = self::router()
                ->getRoute('menu.show', [ ':menu' => $menu[ 'name' ] ]);
        }
        unset($menu);

        return self::template()
                ->createBlock('menu/submenu-menu-list.php', $this->pathViews)
                ->addVars([
                    'key_route' => $nameMenu,
                    'menu'      => $menus
        ]);
    }

    private function createBlockMenuShowForm(string $nameMenu, ?array $query, int $level): Block
    {
        return self::template()
                ->createBlock('menu/content-menu-show_form.php', $this->pathViews)
                ->addNameOverride("menu-show-$nameMenu.php")
                ->addVars([ 'level' => $level, 'menu' => $query ]);
    }

    private function getValidator(ServerRequestInterface $req): Validator
    {
        return (new Validator())
                ->setRules([
                    'description' => 'required|string|max:255',
                    'title'       => 'required|string|max:255|!equal:create'
                ])
                ->setLabels([
                    'description' => t('Description'),
                    'title'       => t('Menu title')
                ])
                ->setInputs($req->getParsedBody());
    }

    private function getData(Validator $validator, ?string $menu = null): array
    {
        $data = [
            'description' => $validator->getInput('description'),
            'title'       => $validator->getInput('title')
        ];

        if ($menu === null) {
            $data[ 'name' ] = Util::strSlug($validator->getInput('title'), '-');
        }

        return $data;
    }
}
