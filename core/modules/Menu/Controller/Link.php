<?php

namespace SoosyzeCore\Menu\Controller;

use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Form\FormLink;

class Link extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create($nameMenu, $req)
    {
        if (!self::menu()->getMenu($nameMenu)->fetch()) {
            return $this->get404($req);
        }
        $values = [];
        $this->container->callHook('menu.link.create.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.store', [ ':menu' => $nameMenu ]);

        $form = (new FormLink([ 'method' => 'post', 'action' => $action ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.link.create.form', [ &$form, $values ]);

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
                    'icon'       => '<i class="fa fa-link" aria-hidden="true"></i>',
                    'title_main' => t('Add a link')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu-link-add.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($nameMenu, $req)
    {
        if (!self::menu()->getMenu($nameMenu)->fetch()) {
            return $this->get404($req);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.link.store.validator', [ &$validator ]);

        $infoUrlOrRoute = self::menu()->getInfo($validator->getInput('link'), $req);

        if ($validator->isValid()) {
            $data = [
                'key'         => $infoUrlOrRoute[ 'key' ],
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $infoUrlOrRoute[ 'link' ],
                'fragment'    => $infoUrlOrRoute[ 'fragment' ],
                'target_link' => $validator->getInput('target_link'),
                'menu'        => $nameMenu,
                'weight'      => 1,
                'parent'      => -1,
                'active'      => true
            ];

            $this->container->callHook('menu.link.store.before', [ $validator, &$data ]);
            self::query()
                ->insertInto('menu_link', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.link.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect(self::router()->getRoute('menu.show', [
                ':menu' => $nameMenu
            ]));
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'inputs' ][ 'link' ]     = $infoUrlOrRoute[ 'link' ];
        $_SESSION[ 'inputs' ][ 'fragment' ] = $infoUrlOrRoute[ 'fragment' ];
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(self::router()->getRoute('menu.link.create', [
            ':menu' => $nameMenu
        ]));
    }

    public function edit($name, $id, $req)
    {
        if (!($values = self::menu()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.link.edit.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.update', [
            ':menu' => $name,
            ':id'   => $id
        ]);

        $form = (new FormLink([ 'method' => 'post', 'action' => $action ]))
            ->setValues($values)
            ->setRewrite(self::router()->isRewrite())
            ->makeFields();

        $this->container->callHook('menu.link.edit.form', [ &$form, $values ]);

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
                    'icon'       => '<i class="fa fa-link" aria-hidden="true"></i>',
                    'title_main' => t('Edit a link')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu-link-edit.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function update($nameMenu, $id, $req)
    {
        if (!self::menu()->find($id)) {
            return $this->get404($req);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.link.update.validator', [ &$validator ]);

        $infoUrlOrRoute = self::menu()->getInfo($validator->getInput('link'), $req);

        if ($validator->isValid()) {
            $data = [
                'key'         => $infoUrlOrRoute[ 'key' ],
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $infoUrlOrRoute[ 'link' ],
                'query'       => $infoUrlOrRoute[ 'query' ],
                'fragment'    => $infoUrlOrRoute[ 'fragment' ],
                'target_link' => $validator->getInput('target_link')
            ];

            $this->container->callHook('menu.link.update.before', [ $validator, &$data ]);
            self::query()
                ->update('menu_link', $data)
                ->where('id', '==', $id)
                ->execute();
            $this->container->callHook('menu.link.update.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect(self::router()->getRoute('menu.show', [
                ':menu' => $nameMenu
            ]));
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'inputs' ][ 'link' ]     = $infoUrlOrRoute[ 'link' ];
        $_SESSION[ 'inputs' ][ 'query' ]    = $infoUrlOrRoute[ 'query' ];
        $_SESSION[ 'inputs' ][ 'fragment' ] = $infoUrlOrRoute[ 'fragment' ];
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(self::router()->getRoute('menu.link.edit', [
            ':menu' => $nameMenu,
            ':id'   => $id
        ]));
    }

    public function delete($name, $id, $req)
    {
        if (!self::menu()->find($id)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'name' => 'required|string|max:255|to_htmlsc',
                'id'   => 'required|int'
            ])
            ->setInputs([ 'name' => $name, 'id' => $id ]);

        $this->container->callHook('menu.link.delete.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('menu.link.delete.before', [ $validator, $id ]);
            self::query()
                ->from('menu_link')
                ->delete()
                ->where('id', '==', $id)
                ->execute();
            $this->container->callHook('menu.link.delete.after', [ $validator, $id ]);
        }

        $route = self::router()->getRoute('menu.show', [ ':menu' => $name ]);

        return new Redirect($route);
    }

    protected function getValidator($req)
    {
        return (new Validator())
                ->setRules([
                    'title_link'      => 'required|string|max:255|to_htmlsc',
                    'icon'            => '!required|max:255|fontawesome:solid,brands',
                    'link'            => 'required|route_or_url',
                    'target_link'     => 'required|inArray:_blank,_self,_parent,_top',
                    'token_link_form' => 'required|token'
                ])
                ->setLabel([
                    'title_link'  => t('Link title'),
                    'link'        => t('Link'),
                    'icon'        => t('Icon'),
                    'target_link' => t('Target'),
                ])
                ->setInputs($req->getParsedBody());
    }
}
