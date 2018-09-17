<?php

namespace Menu\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use Soosyze\Components\Http\Redirect;

define("VIEWS_MENU", MODULES_CORE . 'Menu' . DS . 'Views' . DS);
define("CONFIG_MENU", MODULES_CORE . 'Menu' . DS . 'Config' . DS);

class Menu extends \Soosyze\Controller
{
    protected $pathServices = CONFIG_MENU . 'service.json';

    protected $pathRoutes = CONFIG_MENU . 'routing.json';

    public function show($name)
    {
        $menu = self::menu()->getMenu($name)->fetch();

        if (!$menu) {
            return $this->get404();
        }

        $query = self::menu()
            ->getLinkPerMenu($name)
            ->orderBy('weight')
            ->fetchAll();

        foreach ($query as $key => $link) {
            $query[ $key ][ 'link_edit' ]   = self::router()->getRoute('menu.link.edit', [
                ':menu' => $link[ 'menu' ], ':item' => $link[ 'id' ] ]);
            $query[ $key ][ 'link_delete' ] = self::router()->getRoute('menu.link.delete', [
                ':menu' => $link[ 'menu' ], ':item' => $link[ 'id' ] ]);
        }

        if (isset($_SESSION[ 'inputs' ])) {
            $query = array_merge($query, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        for ($i = 0; $i <= 50; ++$i) {
            $weight[] = [ 'value' => $i, 'label' => $i ];
        }

        $action = self::router()->getRoute('menu.show.check', [ ':item' => $name ]);
        $form   = (new FormBuilder([ 'method' => 'post', 'action' => $action ]));
        foreach ($query as $key => $link) {
            $nameLinkWeight = "weight-" . $link[ 'id' ];
            $nameLinkActive = "active-" . $link[ 'id' ];
            $form->select($nameLinkWeight, $weight, [
                    'selected' => $link[ 'weight' ],
                    'class'    => 'form-control'
                ])
                ->checkbox($nameLinkActive, $nameLinkActive, [ 'checked' => $link[ 'active' ]]);
        }
        $form->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }
        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:red;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        $linkAdd = self::router()->getRoute('menu.link.add', [ ':item' => $name ]);

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></i> Menu'
                ])
                ->render('page.content', 'menu-show.php', VIEWS_MENU, [
                    'menu'     => $query,
                    'form'     => $form,
                    'linkAdd'  => $linkAdd,
                    'menuName' => $menu[ 'title' ]
        ]);
    }

    public function showCheck($name, $r)
    {
        $post = $r->getParsedBody();

        $query = self::menu()->getLinkPerMenu($name)->fetchAll();

        $validator = (new Validator());

        foreach ($query as $link) {
            $keyWeight = "weight-" . $link[ 'id' ];
            $keyActive = "active-" . $link[ 'id' ];
            $validator
                ->addRule($keyActive, 'bool')
                ->addRule($keyWeight, 'required|int|min:0|max:50');
        }

        $validator->setInputs($post);

        if ($validator->isValid()) {
            foreach ($query as $link) {
                $linkUpdate = [
                    'weight' => $validator->getInput("weight-" . $link[ 'id' ]),
                    'active' => ($validator->getInput("active-" . $link[ 'id' ]) == 'on'
                    ? true
                    : false)
                ];

                self::query()
                    ->update('menu_link', $linkUpdate)
                    ->where('id', $link[ 'id' ])
                    ->execute();
            }

            $_SESSION[ 'success' ] = [ 'msg' => 'Votre configuration a été enregistrée.' ];
            $route                 = self::router()->getRoute('menu.show', [ ':item' => $name ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = $validator->getInputs();
        $_SESSION[ 'errors' ]      = $validator->getErrors();
        $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();

        $route = self::router()->getRoute('menu.show', [ ':item' => $name ]);

        return new Redirect($route);
    }

    public function addLink($name)
    {
        $content = [ 'title_link' => '', 'target_link' => '' ];
        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.add.check', [ ':item' => $name ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('menu-add', 'fieldset', function ($form) use ($content) {
                return $form->legend('legen-menu-add', 'Ajouter un lien dans le menu')
                    ->group('menu-add-title', 'div', function ($form) use ($content) {
                        $form->label('label-link-title', 'Titre du lien', [
                            'for' => 'title_link' ])
                        ->text('title_link', 'title_link', [
                            'value'       => $content[ 'title_link' ],
                            'placeholder' => 'Exemple: Ma page 1',
                            'class'       => 'form-control',
                            'required'    => 1
                        ]);
                    }, [ 'class' => "form-group" ])
                    ->group('menu-add-link', 'div', function ($form) use ($content) {
                        $form->label('label-link', 'Lien')
                        ->text('link', 'link', [
                            'value'       => $content[ 'target_link' ],
                            'placeholder' => 'Exemple: http://monsite.fr/node/1',
                            'class'       => 'form-control',
                            'required'    => 1
                        ]);
                    }, [ 'class' => "form-group" ]);
            })
            ->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => 'Menu'
                ])
                ->render('page.content', 'menu-link-add.php', VIEWS_MENU, [
                    'form' => $form
        ]);
    }

    public function addLinkCheck($nameMenu, $r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'title_link' => 'required|string|max:255|striptags',
                'link'       => 'required|string|max:255|htmlsc',
                'token'      => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $link = [
                $validator->getInput('title_link'),
                $validator->getInput('link'),
                $nameMenu,
                1,
                -1,
                true
            ];

            self::query()
                ->insertInto('menu_link', [ 'title_link', 'target_link', 'menu',
                    'weight',
                    'parent', 'active' ])
                ->values($link)
                ->execute();

            $_SESSION[ 'success' ] = [ 'msg' => 'Votre configuration a été enregistrée.' ];
            $route                 = self::router()->getRoute('menu.show', [ ':item' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = $validator->getInputs();
        $_SESSION[ 'errors' ]      = $validator->getErrors();
        $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();

        $route = self::router()->getRoute('menu.link.add', [ ':item' => $nameMenu ]);

        return new Redirect($route);
    }

    public function editLink($name, $id, $r)
    {
        $query = self::query()
            ->from('menu_link')
            ->where('id', '==', $id)
            ->fetch();

        if (!$query) {
            return $this->get404();
        }

        if (isset($_SESSION[ 'inputs' ])) {
            $query = array_merge($query, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.edit.check', [ ':menu' => $name,
            ':item' => $id ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('menu-add', 'fieldset', function ($form) use ($query) {
                return $form->legend('legend-menu-add', 'Ajouter un lien dans le menu')
                    ->group('menu-add-title', 'div', function ($form) use ($query) {
                        $form->label('label-link-title', 'Titre du lien')
                        ->text('title_link', 'title_link', [
                            'value'       => $query[ 'title_link' ],
                            'placeholder' => 'Exemple: Ma page 1',
                            'class'       => 'form-control',
                            'required'    => 1
                        ]);
                    }, [ 'class' => "form-group" ])
                    ->group('menuu-add-link', 'div', function ($form) use ($query) {
                        $form->label('label-link', 'Lien')
                        ->text('link', 'link', [
                            'value'       => $query[ 'target_link' ],
                            'placeholder' => 'Exemple: http://monsite.fr/node/1',
                            'class'       => 'form-control',
                            'required'    => 1
                        ]);
                    }, [ 'class' => "form-group" ]);
            })
            ->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => 'Menu'
                ])
                ->render('page.content', 'menu-link-edit.php', VIEWS_MENU, [
                    'form' => $form
        ]);
    }

    public function editLinkCheck($nameMenu, $id, $r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'title_link' => 'required|string|max:255|striptags',
                'link'       => 'required|string|max:255|htmlsc',
                'token'      => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $link = [
                'title_link'  => $validator->getInput('title_link'),
                'target_link' => $validator->getInput('link')
            ];
            self::query()
                ->update('menu_link', $link)
                ->where('id', '==', $id)
                ->execute();

            $_SESSION[ 'success' ] = [ 'msg' => 'Votre configuration a été enregistrée.' ];
            $route                 = self::router()->getRoute('menu.show', [ ':item' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = $validator->getInputs();
        $_SESSION[ 'errors' ]      = $validator->getErrors();
        $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();

        $route = self::router()->getRoute('menu.link.edit', [
            ':menu' => $nameMenu,
            ':item' => $id
        ]);

        return new Redirect($route);
    }

    public function deleteLink($name, $id, $r)
    {
        $validator = (new Validator())
            ->setRules([
                'name' => 'required|string|max:255|htmlsc',
                'id'   => 'required|int'
            ])
            ->setInputs([ 'name' => $name, 'id' => $id ]);

        if ($validator->isValid()) {
            self::query()
                ->from('menu_link')
                ->delete()
                ->where('id', '==', $id)
                ->execute();
        }

        $route = self::router()->getRoute('menu.show', [ ':item' => $name ]);

        return new Redirect($route);
    }
}
