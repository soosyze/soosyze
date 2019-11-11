<?php

namespace SoosyzeCore\Menu\Controller;

use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Form\FormLink;

class Link extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function create($nameMenu, $req)
    {

        $content = [];
        $this->container->callHook('menu.link.create.form.data', [ &$content ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.store', [ ':menu' => $nameMenu ]);

        $form = (new FormLink([ 'method' => 'post', 'action' => $action ]))
            ->content($content)
            ->make();

        $this->container->callHook('menu.link.create.form', [ &$form, $content ]);

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
                    'title_main' => '<i class="fa fa-bars" aria-hidden="true"></i> ' . t('Add a link')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu-link-add.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($nameMenu, $req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'title_link'        => 'required|string|max:255|striptags',
                'link'              => 'required',
                'icon'              => '!required|max:255|fontawesome:solid,brands',
                'target_link'       => 'required|inArray:_blank,_self,_parent,_top',
                'token_link_create' => 'required|token'
            ])
            ->setLabel([
                'title_link'        => t('Link title'),
                'link'              => t('Link'),
                'icon'              => t('Icon'),
                'target_link'       => t('Target'),
            ])
            ->setInputs($post);

        $isUrlOrRoute = self::menu()->isUrlOrRoute($post, $req->withMethod('GET'));

        $this->container->callHook('menu.link.store.validator', [ &$validator ]);

        if ($validator->isValid() && $isUrlOrRoute !== false) {
            $data = [
                'key'         => $isUrlOrRoute['key'],
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $isUrlOrRoute['link'],
                'fragment'    => $isUrlOrRoute['fragment'],
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
            $route                 = self::router()->getRoute('menu.show', [ ':menu' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute['is_valid']) {
            $_SESSION[ 'messages' ][ 'errors' ][ 'link.route' ] = t('Link value is not a URL or a route');
            $_SESSION[ 'errors_keys' ][]                        = 'link';
        }

        $route = self::router()->getRoute('menu.link.create', [ ':menu' => $nameMenu ]);

        return new Redirect($route);
    }

    public function edit($name, $id, $req)
    {
        if (!($query = self::menu()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.link.edit.form.data', [ &$query ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $query = array_merge($query, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.update', [
            ':menu' => $name,
            ':id' => $id
        ]);

        $form = (new FormLink([ 'method' => 'post', 'action' => $action ]))
            ->content($query)
            ->make();

        $this->container->callHook('menu.link.edit.form', [ &$form, $query ]);

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
                    'title_main' => '<i class="fa fa-bars" aria-hidden="true"></i> ' . t('Edit a link')
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

        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'title_link'      => 'required|string|max:255|htmlsc',
                'icon'            => '!required|max:255|fontawesome:solid,brands',
                'link'            => 'required',
                'target_link'     => 'required|inArray:_blank,_self,_parent,_top',
                'token_link_edit' => 'required|token'
            ])
            ->setLabel([
                'title_link'        => t('Link title'),
                'link'              => t('Link'),
                'icon'              => t('Icon'),
                'target_link'       => t('Target'),
            ])
            ->setInputs($post);

        $isUrlOrRoute = self::menu()->isUrlOrRoute($post, $req->withMethod('GET'));

        $this->container->callHook('menu.link.update.validator', [ &$validator ]);

        if ($validator->isValid() && $isUrlOrRoute !== false) {
            $data = [
                'key'         => $isUrlOrRoute['key'],
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $isUrlOrRoute['link'],
                'fragment'    => $isUrlOrRoute['fragment'],
                'target_link' => $validator->getInput('target_link')
            ];

            $this->container->callHook('menu.link.update.before', [ $validator, &$data ]);
            self::query()
                ->update('menu_link', $data)
                ->where('id', '==', $id)
                ->execute();
            $this->container->callHook('menu.link.update.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
            $route                               = self::router()->getRoute('menu.show', [
                ':menu' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute['is_valid']) {
            $_SESSION[ 'messages' ][ 'errors' ][ 'link.route' ] = t('Link value is not a URL or a route');
            $_SESSION[ 'errors_keys' ][]                        = 'link';
        }

        $route = self::router()->getRoute('menu.link.edit', [
            ':menu' => $nameMenu,
            ':id' => $id
        ]);

        return new Redirect($route);
    }

    public function delete($name, $id, $req)
    {
        if (!self::menu()->find($id)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'name' => 'required|string|max:255|htmlsc',
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
    
    protected static function getTarget()
    {
        return [
            [ 'value' => '_blank', 'label' => '(_blank) ' . t('Load in a new window') ],
            [ 'value' => '_self', 'label' => '(_self) ' . t('Load in the same window') ],
            [ 'value' => '_parent', 'label' => '(_parent) ' . t('Load into the parent frameset') ],
            [ 'value' => '_top', 'label' => '(_top) ' . t('Load in the whole body of the window') ]
        ];
    }
}
