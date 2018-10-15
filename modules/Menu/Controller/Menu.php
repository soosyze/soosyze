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

    public function show($name, $req)
    {
        $menu = self::menu()->getMenu($name)->fetch();

        if (!$menu) {
            return $this->get404($req);
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

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
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

    public function showCheck($name, $req)
    {
        if (!($links = self::menu()->getLinkPerMenu($name)->fetchAll())) {
            $_SESSION[ 'errors' ] = [ 'Impossible d\'enregistrer la configuration, le menu '. $name . ' n\'existe pas.' ];
            $route                = self::router()->getRoute('menu.show', [ ':item' => $name ]);

            return new Redirect($route);
        }

        $validator = (new Validator());

        foreach ($links as $link) {
            $validator->addRule('active-' . $link[ 'id' ], 'bool')
                ->addRule('weight-' . $link[ 'id' ], 'required|int|min:0|max:50');
        }
        
        $post = $req->getParsedBody();
        $validator->setInputs($post);

        if ($validator->isValid()) {
            foreach ($links as $link) {
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

            $_SESSION[ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
            $route                 = self::router()->getRoute('menu.show', [ ':item' => $name ]);
        } else {
            $_SESSION[ 'inputs' ]      = $validator->getInputs();
            $_SESSION[ 'errors' ]      = $validator->getErrors();
            $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();
            $route                     = self::router()->getRoute('menu.show', [
                ':item' => $name ]);
        }

        return new Redirect($route);
    }
}
