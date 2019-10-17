<?php

namespace SoosyzeCore\Menu\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

class Menu extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->dirRoutes    = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function show($name, $req)
    {
        if (!($menu = self::menu()->getMenu($name)->fetch())) {
            return $this->get404($req);
        }

        $action = self::router()->getRoute('menu.show.check', [ ':menu' => $name ]);
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
                    'title_main' => '<i class="fa fa-bars" aria-hidden="true"></i> ' . t('Menu')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-menu-show.php', $this->pathViews, [
                    'form'     => $form,
                    'menu'     => $this->renderMenu($req, $name),
                    'linkAdd'  => self::router()->getRoute('menu.link.create', [
                        ':menu' => $name
                    ]),
                    'menuName' => $menu[ 'title' ]
                ])
                ->make('content.submenu', 'submenu-menu.php', $this->pathViews, [
                    'menu' => $this->renderSubMenu(),
                    'id' =>$name
        ]);
    }

    public function showCheck($name, $req)
    {
        $route = self::router()->getRoute('menu.show', [ ':menu' => $name ]);
        if (!($links = self::menu()->getLinkPerMenu($name)->fetchAll())) {
            return new Redirect($route);
        }

        $post      = $req->getParsedBody();
        $validator = new Validator();
        foreach ($links as $link) {
            $validator
                ->addRule("active-{$link[ 'id' ]}", 'bool')
                ->addRule("parent-{$link[ 'id' ]}", 'required|int')
                ->addRule("weight-{$link[ 'id' ]}", 'required|int|min:1|max:50');
        }
        $validator->addRule('token_menu', 'token')
            ->setInputs($post);

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
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        }

        return new Redirect($route);
    }

    public function renderMenu($req, $nameMenu, $parent = -1, $level = 1)
    {
        $query = self::query()
            ->from('menu_link')
            ->where('menu', $nameMenu)
            ->where('parent', '==', $parent)
            ->orderBy('weight')
            ->fetchAll();

        $isRewite = self::core()->get('config')->get('settings.rewrite_engine');
        foreach ($query as &$link) {
            $req_link        = $req->withUri(
                $req->getUri()
                ->withQuery('q=' . $link[ 'link' ])
                ->withFragment($link[ 'fragment' ])
            );
            $link[ 'link' ]        = $this->get('menu.hook.app')->rewiteUri($isRewite, $req_link);
            $link[ 'link_edit' ]   = self::router()
                ->getRoute('menu.link.edit', [ ':menu' => $link[ 'menu' ], ':item' => $link[ 'id' ] ]);
            $link[ 'link_delete' ] = self::router()
                ->getRoute('menu.link.delete', [ ':menu' => $link[ 'menu' ], ':item' => $link[ 'id' ] ]);
            $link[ 'submenu' ]     = $this->renderMenu($req, $nameMenu, $link[ 'id' ], $level + 1);
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
            $menu[ 'link' ]   = self::router()
                ->getRoute('menu.show', [ ':menu' => $menu[ 'name' ] ]);
        }

        return $menus;
    }
}
