<?php

namespace SoosyzeCore\Menu\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Form\FormMenu;

class Menu extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function index($req)
    {
        if ($menu = self::query()->from('menu')->fetch()) {
            return $this->show($menu[ 'name' ], $req);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Menu')
                ])
                ->make('page.content', 'page-not-menu-show.php', $this->pathViews, [
                    'menu_add'    => self::router()->getRoute('menu.create')
                ]);
    }

    public function show($name, $req)
    {
        if (!($menu = self::menu()->getMenu($name)->fetch())) {
            return $this->get404($req);
        }

        $action = self::router()->getRoute('menu.check', [ ':menu' => $name ]);
        $form   = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
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
                ->make('page.content', 'page-menu-show.php', $this->pathViews, [
                    'form'        => $form,
                    'menu'        => $this->renderMenu($name),
                    'menu_add'    => self::router()->getRoute('menu.create'),
                    'menu_edit'   => self::router()->getRoute('menu.edit', [
                        ':menu' => $name
                    ]),
                    'menu_delete' => self::router()->getRoute('menu.delete', [
                        ':menu' => $name
                    ]),
                    'link_add'    => self::router()->getRoute('menu.link.create', [
                        ':menu' => $name
                    ]),
                    'menuName'    => $menu[ 'title' ]
                ])
                ->make('content.submenu', 'submenu-menu.php', $this->pathViews, [
                    'menu' => $this->renderSubMenu(),
                    'id'   => $name
        ]);
    }

    public function check($name, $req)
    {
        $route = self::router()->getRoute('menu.show', [ ':menu' => $name ]);
        if (!($links = self::menu()->getLinkPerMenu($name)->fetchAll())) {
            return new Redirect($route);
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
            foreach ($links as $link) {
                $linkUpdate = [
                    'active' => (bool) ($validator->getInput("active-{$link[ 'id' ]}") === 'on'),
                    'parent' => (int) $validator->getInput("parent-{$link[ 'id' ]}"),
                    'weight' => (int) $validator->getInput("weight-{$link[ 'id' ]}")
                ];

                self::query()
                    ->update('menu_link', $linkUpdate)
                    ->where('id', $link[ 'id' ])
                    ->execute();
            }

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        }

        return new Redirect($route);
    }

    public function create($req)
    {
        $values = [];
        $this->container->callHook('menu.create.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values += $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.store');

        $form = (new FormMenu([ 'method' => 'post', 'action' => $action ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.create.form', [ &$form, $values ]);

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
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Add a menu')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu-link-add.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($req)
    {
        $validator = $this->getValidator($req);

        $this->container->callHook('menu.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = [
                'title'       => $validator->getInput('title'),
                'name'        => Util::strSlug($validator->getInput('title'), '-'),
                'description' => $validator->getInput('description')
            ];

            $this->container->callHook('menu.store.before', [ $validator, &$data ]);
            self::query()
                ->insertInto('menu', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect(
                self::router()->getRoute('menu.show', [ ':menu' => $data[ 'name' ] ])
            );
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(self::router()->getRoute('menu.create'));
    }

    public function edit($menu, $req)
    {
        if (!($values = self::menu()->getMenu($menu)->fetch())) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.store.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values += $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.update', [ ':menu' => $menu ]);

        $form = (new FormMenu([ 'method' => 'post', 'action' => $action ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.store.form', [ &$form, $values ]);

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
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Edit a menu')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu-link-add.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function update($menu, $req)
    {
        if (!self::menu()->getMenu($menu)->fetch()) {
            return $this->get404($req);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.update.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = [
                'title'       => $validator->getInput('title'),
                'description' => $validator->getInput('description')
            ];

            $this->container->callHook('menu.update.before', [ $validator, &$data ]);
            self::query()
                ->update('menu', $data)
                ->where('name', '==', $menu)
                ->execute();
            $this->container->callHook('menu.update.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect(
                self::router()->getRoute('menu.show', [ ':menu' => $menu ])
            );
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(self::router()->getRoute('menu.edit'));
    }

    public function delete($menu, $req)
    {
        if (!self::menu()->getMenu($menu)->fetch()) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'name' => 'required|string|max:255|to_htmlsc',
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
                ->where('menu', '==', $menu)
                ->execute();
            self::query()
                ->from('menu')
                ->delete()
                ->where('name', '==', $menu)
                ->execute();
            $this->container->callHook('menu.delete.after', [ $validator, $menu ]);

            return new Redirect(self::router()->getRoute('menu.index'));
        }

        return new Redirect(
            self::router()->getRoute('menu.show', [
                ':menu' => $menu
            ])
        );
    }

    public function renderMenu($nameMenu, $parent = -1, $level = 1)
    {
        $query = self::query()
            ->from('menu_link')
            ->where('menu', $nameMenu)
            ->where('parent', '==', $parent)
            ->orderBy('weight')
            ->fetchAll();

        foreach ($query as &$link) {
            $link[ 'link_edit' ]   = self::router()
                ->getRoute('menu.link.edit', [ ':menu' => $link[ 'menu' ], ':id' => $link[ 'id' ] ]);
            $link[ 'link_delete' ] = self::router()
                ->getRoute('menu.link.delete', [ ':menu' => $link[ 'menu' ], ':id' => $link[ 'id' ] ]);
            $link[ 'submenu' ]     = $this->renderMenu($nameMenu, $link[ 'id' ], $level + 1);

            if (!$link[ 'key' ]) {
                continue;
            }

            $link[ 'link' ] = self::menu()->rewiteUri($link);
        }

        return self::template()
                ->createBlock('menu-show.php', $this->pathViews)
                ->nameOverride("menu-show-$nameMenu.php")
                ->addVars([ 'menu' => $query, 'level' => $level ]);
    }

    public function renderSubMenu()
    {
        $menus = self::query()
            ->from('menu')
            ->fetchAll();

        foreach ($menus as &$menu) {
            $menu[ 'link_show' ] = self::router()
                ->getRoute('menu.show', [ ':menu' => $menu[ 'name' ] ]);
        }

        return $menus;
    }

    private function getValidator($req)
    {
        return (new Validator)
                ->setRules([
                    'title'       => 'required|string|max:255|!equal:create',
                    'description' => 'required|string|max:255'
                ])
                ->setLabel([
                    'title'       => t('Menu title'),
                    'description' => t('Description')
                ])
                ->setInputs($req->getParsedBody());
    }
}
