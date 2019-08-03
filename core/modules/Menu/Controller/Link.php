<?php

namespace SoosyzeCore\Menu\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

class Link extends \Soosyze\Controller
{
    private static $optionTarget = [
        [ 'value' => '_blank', 'label' => '(_blank) Charger dans une nouvelle fenêtre' ],
        [ 'value' => '_self', 'label' => '(_self) Charger dans le même cadre que celui sur lequel vous avez cliqué' ],
        [ 'value' => '_parent', 'label' => '(_parent) Charger dans le frameset parent' ],
        [ 'value' => '_top', 'label' => '(_top) Charge dans le corps entier de la fenêtre' ]
    ];

    public function __construct()
    {
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function create($nameMenu)
    {
        $content = [ 'title_link' => '', 'icon' => '', 'link' => '', 'target_link' => '_self' ];

        $this->container->callHook('menu.link.create.form.data', [ &$content ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.store', [ ':menu' => $nameMenu ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('menu-link-fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('menu-link-legend', 'Ajouter un lien dans le menu')
                ->group('menu-link-title-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-title-label', 'Titre du lien', [
                        'for' => 'title_link' ])
                    ->text('title_link', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'Exemple: Ma page 1',
                        'required'    => 1,
                        'value'       => $content[ 'title_link' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-link-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-link-label', 'Lien')
                    ->text('link', [
                        'class'       => 'form-control',
                        'placeholder' => 'Exemple: node/1 ou http://site-externe.fr/',
                        'required'    => 1,
                        'value'       => $content[ 'link' ],
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-icon-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-icon-label', 'Icon', [
                        'data-tooltip' => 'Les icônes sont créées à partir des class CSS de FontAwesome'
                    ])
                    ->text('icon', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'CSS fontAwesome : fa fa-bars, fa fa-home...',
                        'value'       => $content[ 'icon' ],
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-target-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-target-label', 'Cîble')
                    ->select('target_link', self::$optionTarget, [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $content[ 'target_link' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_link_create')
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

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
                    'title_main' => '<i class="fa fa-bars" aria-hidden="true"></i> Ajouter un lien'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'menu-link-add.php', $this->pathViews, [
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
                'icon'              => '!required|string|max:255|striptags',
                'target_link'       => 'required|inArray:_blank,_self,_parent,_top',
                'token_link_create' => 'required|token'
            ])
            ->setInputs($post);

        $isUrlOrRoute = self::menu()->isUrlOrRoute(
            $post[ 'link' ],
            $req->withMethod('GET')
        );

        $this->container->callHook('menu.link.store.validator', [ &$validator ]);

        if ($validator->isValid() && $isUrlOrRoute) {
            $data = [
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $validator->getInput('link'),
                'target_link' => $validator->getInput('target_link'),
                'menu'        => $nameMenu,
                'weight'      => 1,
                'parent'      => -1,
                'active'      => true
            ];
            if (isset($isUrlOrRoute[ 'key' ])) {
                $data[ 'key' ] = $isUrlOrRoute[ 'key' ];
            }

            $this->container->callHook('menu.link.store.before', [ &$validator, &$data ]);
            self::query()
                ->insertInto('menu_link', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.link.store.after', [ &$validator ]);

            $_SESSION[ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
            $route                 = self::router()->getRoute('menu.show', [ ':menu' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute) {
            $_SESSION[ 'messages' ][ 'errors' ][ 'link.route' ] = 'La valeur de link n\'est pas une URL ou une route';
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
            ':item' => $id
        ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('menu-link-fieldset', 'fieldset', function ($form) use ($query) {
                $form->legend('menu-link-legend', 'Éditer un lien dans le menu')
                ->group('menu-link-title-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-title-label', 'Titre du lien')
                    ->text('title_link', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'Exemple: Ma page 1',
                        'required'    => 1,
                        'value'       => $query[ 'title_link' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-link-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-link-label', 'Lien')
                    ->text('link', [
                        'class'       => 'form-control',
                        'placeholder' => 'Exemple: node/1 ou http://site-externe.fr/',
                        'required'    => 1,
                        'value'       => $query[ 'link' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-icon-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-icon-label', 'Icon', [
                        'data-tooltip' => 'Les icônes sont créées à partir des class CSS de FontAwesome'
                    ])
                    ->text('icon', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'CSS fontAwesome : fa fa-bars, fa fa-home...',
                        'value'       => $query[ 'icon' ],
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-target-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-target-label', 'Cîble')
                    ->select('target_link', self::$optionTarget, [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $query[ 'target_link' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_link_edit')
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

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
                    'title_main' => '<i class="fa fa-bars" aria-hidden="true"></i> Éditer un lien'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'menu-link-edit.php', $this->pathViews, [
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
                'icon'            => '!required|string|max:255|htmlsc',
                'link'            => 'required',
                'target_link'     => 'required|inArray:_blank,_self,_parent,_top',
                'token_link_edit' => 'required|token'
            ])
            ->setInputs($post);

        $isUrlOrRoute = self::menu()->isUrlOrRoute(
            $post[ 'link' ],
            $req->withMethod('GET')
        );

        $this->container->callHook('menu.link.update.validator', [ &$validator ]);

        if ($validator->isValid() && $isUrlOrRoute) {
            $data = [
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $validator->getInput('link'),
                'target_link' => $validator->getInput('target_link')
            ];
            if (isset($isUrlOrRoute[ 'key' ])) {
                $data[ 'key' ] = $isUrlOrRoute[ 'key' ];
            }

            $this->container->callHook('menu.link.update.before', [ &$validator, &$data ]);
            self::query()
                ->update('menu_link', $data)
                ->where('id', '==', $id)
                ->execute();
            $this->container->callHook('menu.link.update.after', [ &$validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
            $route                               = self::router()->getRoute('menu.show', [
                ':menu' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute) {
            $_SESSION[ 'messages' ][ 'errors' ][ 'link.route' ] = 'La valeur de link n\'est pas une URL ou une route';
            $_SESSION[ 'errors_keys' ][]                        = 'link';
        }

        $route = self::router()->getRoute('menu.link.edit', [
            ':menu' => $nameMenu,
            ':item' => $id
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
}
