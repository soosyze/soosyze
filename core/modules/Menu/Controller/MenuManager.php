<?php

namespace SoosyzeCore\Menu\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Template\Services\Block;

/**
 * @method \SoosyzeCore\Menu\Services\Menu           menu()
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 *
 * @phpstan-import-type MenuEntity from \SoosyzeCore\Menu\Extend
 * @phpstan-import-type MenuLinkEntity from \SoosyzeCore\Menu\Extend
 */
class MenuManager extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin(ServerRequestInterface $req): ResponseInterface
    {
        return $this->show('menu-main', $req);
    }

    public function show(string $name, ServerRequestInterface $req): ResponseInterface
    {
        /** @phpstan-var MenuEntity|null $menu */
        $menu = self::menu()->getMenu($name)->fetch();
        if ($menu === null) {
            return $this->get404($req);
        }

        $action = self::router()->generateUrl('menu.check', [ ':menu' => $name ]);

        $form = (new FormBuilder([ 'action' => $action, 'class' => 'form-api', 'method' => 'patch' ]))
            ->group('submit-group', 'div', function ($form) {
                $form->token('token_menu')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
            });

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t($menu[ 'title' ])
                ])
                ->view('page.submenu', self::menu()->getMenuSubmenu('menu.show', $menu[ 'name' ]))
                ->make('page.content', 'menu/content-menu-show.php', $this->pathViews, [
                    'form'              => $form,
                    'link_create_link'  => self::router()->generateUrl('menu.link.create', [
                        ':menu' => $name
                    ]),
                    'link_create_menu'  => self::router()->generateUrl('menu.create'),
                    'list_menu_submenu' => $this->getListMenuSubmenu($name),
                    'menu'              => $this->renderMenu($name),
                    'menu_name'         => $menu[ 'title' ]
        ]);
    }

    public function check(string $name, ServerRequestInterface $req): ResponseInterface
    {
        $route = self::router()->generateUrl('menu.show', [ ':menu' => $name ]);
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
            ->setInputs((array) $req->getParsedBody());

        if ($validator->isValid()) {
            $updateParents = [];
            foreach ($links as $link) {
                $data = [
                    'active'       => (bool) $validator->getInput("active-{$link[ 'id' ]}"),
                    'has_children' => false,
                    'parent'       => $validator->getInputInt("parent-{$link[ 'id' ]}"),
                    'weight'       => $validator->getInputInt("weight-{$link[ 'id' ]}")
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

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [ 'redirect' => $route ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function getListMenuSubmenu(string $nameMenu): Block
    {
        $menus = self::query()
            ->from('menu')
            ->fetchAll();

        foreach ($menus as &$menu) {
            $menu[ 'link' ] = self::router()
                ->generateUrl('menu.show', [ ':menu' => $menu[ 'name' ] ]);
        }
        unset($menu);

        return self::template()
                ->createBlock('menu/submenu-menu-list.php', $this->pathViews)
                ->addVars([
                    'key_route' => $nameMenu,
                    'menu'      => $menus
        ]);
    }

    private function renderMenu(string $nameMenu, int $parent = -1, int $level = 1): Block
    {
        /** @phpstan-var array<MenuLinkEntity> $query */
        $query = self::query()
            ->from('menu_link')
            ->where('menu', '=', $nameMenu)
            ->where('parent', '=', $parent)
            ->orderBy('weight')
            ->fetchAll();

        foreach ($query as &$link) {
            $link[ 'link_edit' ]   = self::router()
                ->generateUrl('menu.link.edit', [ ':menu' => $link[ 'menu' ], ':id' => $link[ 'id' ] ]);
            $link[ 'link_remove' ] = self::router()
                ->generateUrl('menu.link.remove.modal', [ ':menu' => $link[ 'menu' ], ':id' => $link[ 'id' ] ]);
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

    private function createBlockMenuShowForm(string $nameMenu, ?array $query, int $level): Block
    {
        return self::template()
                ->createBlock('menu/content-menu-show_form.php', $this->pathViews)
                ->addNameOverride("menu-show-$nameMenu.php")
                ->addVars([ 'level' => $level, 'menu' => $query ]);
    }
}
