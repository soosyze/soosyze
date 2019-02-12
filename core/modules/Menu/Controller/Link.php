<?php

namespace Menu\Controller;

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

    public function create($nameMenu)
    {
        $content = [ 'title_link' => '', 'link' => '', 'target_link' => '_self' ];
        
        $this->container->callHook('menu.link.create.form.data', [ &$content ]);
        
        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.store', [ ':item' => $nameMenu ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('menu-link-fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('menu-link-legend', 'Ajouter un lien dans le menu')
                ->group('menu-link-title-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-title-label', 'Titre du lien', [
                        'for' => 'title_link' ])
                    ->text('title_link', 'title_link', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'Exemple: Ma page 1',
                        'required'    => 1,
                        'value'       => $content[ 'title_link' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-link-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-link-label', 'Lien')
                    ->text('link', 'link', [
                        'class'       => 'form-control',
                        'placeholder' => 'Exemple: node/1 ou http://site-externe.fr/',
                        'required'    => 1,
                        'value'       => $content[ 'link' ],
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-target-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-target-label', 'Cîble')
                    ->select('target_link', 'target_link', self::$optionTarget, [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $content[ 'target_link' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
            
        $this->container->callHook('menu.link.create.form', [ &$form, $content ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => 'Menu'
                ])
                ->render('page.content', 'menu-link-add.php', VIEWS_MENU, [
                    'form' => $form
        ]);
    }

    public function store($nameMenu, $req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'title_link'  => 'required|string|max:255|striptags',
                'link'        => 'required',
                'target_link' => 'required|inArray:_blank,_self,_parent,_top',
                'token'       => 'required|token'
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
                'link'        => $validator->getInput('link'),
                'target_link' => $validator->getInput('target_link'),
                'menu'        => $nameMenu,
                'weight'      => 1,
                'parent'      => -1,
                'active'      => true
            ];
            if (isset($isUrlOrRoute[ 'key' ])) {
                $data[ 'key' ] = $isUrlOrRoute['key'];
            }

            $this->container->callHook('menu.link.store.before', [ &$validator, &$data ]);
            self::query()
                ->insertInto('menu_link', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.link.store.after', [ &$validator ]);

            $_SESSION[ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
            $route                 = self::router()->getRoute('menu.show', [ ':item' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = $validator->getInputs();
        $_SESSION[ 'errors' ]      = $validator->getErrors();
        $_SESSION[ 'errors_keys' ] = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute) {
            $_SESSION[ 'errors' ][ 'link.route' ] = 'La valeur de link n\'est pas une URL ou une route';
            $_SESSION[ 'errors_keys' ][]          = 'link';
        }

        $route = self::router()->getRoute('menu.link.add', [ ':item' => $nameMenu ]);

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
                return $form->legend('menu-link-legend', 'Éditer un lien dans le menu')
                    ->group('menu-link-title-group', 'div', function ($form) use ($query) {
                        $form->label('menu-link-title-label', 'Titre du lien')
                        ->text('title_link', 'title_link', [
                            'class'       => 'form-control',
                            'placeholder' => 'Exemple: Ma page 1',
                            'required'    => 1,
                            'value'       => $query[ 'title_link' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('menu-link-link-group', 'div', function ($form) use ($query) {
                        $form->label('menu-link-link-label', 'Lien')
                        ->text('link', 'link', [
                            'class'       => 'form-control',
                            'placeholder' => 'Exemple: node/1 ou http://site-externe.fr/',
                            'required'    => 1,
                            'value'       => $query[ 'link' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('menu-link-target-group', 'div', function ($form) use ($query) {
                        $form->label('menu-link-target-label', 'Cîble')
                        ->select('target_link', 'target_link', self::$optionTarget, [
                            'class'    => 'form-control',
                            'required' => 1,
                            'selected' => $query[ 'target_link' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
        
        $this->container->callHook('menu.link.edit.form', [ &$form, $query ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => 'Menu'
                ])
                ->render('page.content', 'menu-link-edit.php', VIEWS_MENU, [
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
                'title_link'  => 'required|string|max:255|striptags',
                'link'        => 'required',
                'target_link' => 'required|inArray:_blank,_self,_parent,_top',
                'token'       => 'required|token'
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

            $_SESSION[ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
            $route                 = self::router()->getRoute('menu.show', [ ':item' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = $validator->getInputs();
        $_SESSION[ 'errors' ]      = $validator->getErrors();
        $_SESSION[ 'errors_keys' ] = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute) {
            $_SESSION[ 'errors' ][ 'link.route' ] = 'La valeur de link n\'est pas une URL ou une route';
            $_SESSION[ 'errors_keys' ][]          = 'link';
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

        $route = self::router()->getRoute('menu.show', [ ':item' => $name ]);

        return new Redirect($route);
    }
}
