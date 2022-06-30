<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Enum\Menu;
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
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function show(
        ServerRequestInterface $req,
        int $menuId = Menu::MAIN_MENU
    ): ResponseInterface {
        /** @phpstan-var MenuEntity|null $menu */
        $menu = self::menu()->getMenu($menuId)->fetch();
        if ($menu === null) {
            return $this->get404($req);
        }

        $action = self::router()->generateUrl('menu.check', [ 'menuId' => $menuId ]);

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
                ->view('page.submenu', self::menu()->getMenuSubmenu('menu.show', $menu[ 'menu_id' ]))
                ->make('page.content', 'menu/content-menu-show.php', $this->pathViews, [
                    'form'              => $form,
                    'link_create_link'  => self::router()->generateUrl('menu.link.create', [
                        'menuId' => $menuId
                    ]),
                    'link_create_menu'  => self::router()->generateUrl('menu.create'),
                    'list_menu_submenu' => $this->getListMenuSubmenu($menuId),
                    'menu'              => $this->renderMenu($menuId),
                    'menu_name'         => $menu[ 'title' ]
        ]);
    }

    public function check(int $menuId, ServerRequestInterface $req): ResponseInterface
    {
        $route = self::router()->generateUrl('menu.show', [ 'menuId' => $menuId ]);
        if (!($links = self::menu()->getLinkPerMenu($menuId)->fetchAll())) {
            return $this->json(200, [ 'redirect' => $route ]);
        }

        $validator = new Validator();
        foreach ($links as $link) {
            $validator
                ->addRule("active-{$link[ 'link_id' ]}", 'bool')
                ->addRule("parent-{$link[ 'link_id' ]}", 'required|numeric')
                ->addRule("weight-{$link[ 'link_id' ]}", 'required|between_numeric:1,50');
        }
        $validator->addRule('token_menu', 'token')
            ->setInputs((array) $req->getParsedBody());

        if ($validator->isValid()) {
            $updateParents = [];
            foreach ($links as $link) {
                $data = [
                    'active'       => (bool) $validator->getInput("active-{$link[ 'link_id' ]}"),
                    'has_children' => false,
                    'parent'       => $validator->getInputInt("parent-{$link[ 'link_id' ]}"),
                    'weight'       => $validator->getInputInt("weight-{$link[ 'link_id' ]}")
                ];

                self::query()
                    ->update('menu_link', $data)
                    ->where('link_id', '=', $link[ 'link_id' ])
                    ->execute();

                if ($data[ 'parent' ] >= 1 && !in_array($data[ 'parent' ], $updateParents)) {
                    $updateParents[] = $data[ 'parent' ];
                }
            }
            /* Mise à jour des parents. */
            foreach ($updateParents as $parent) {
                self::query()
                    ->update('menu_link', [ 'has_children' => true ])
                    ->where('link_id', '=', $parent)
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

    private function getListMenuSubmenu(int $menuId): Block
    {
        $menus = self::query()
            ->from('menu')
            ->fetchAll();

        foreach ($menus as &$menu) {
            $menu[ 'link' ] = self::router()
                ->generateUrl('menu.show', [ 'menuId' => $menu[ 'menu_id' ] ]);
        }
        unset($menu);

        return self::template()
                ->createBlock('menu/submenu-menu-list.php', $this->pathViews)
                ->addVars([
                    'key_route' => $menuId,
                    'menu'      => $menus
        ]);
    }

    private function renderMenu(int $menuId, int $parent = -1, int $level = 1): Block
    {
        /** @phpstan-var array<MenuLinkEntity> $query */
        $query = self::query()
            ->from('menu_link')
            ->where('menu_id', '=', $menuId)
            ->where('parent', '=', $parent)
            ->orderBy('weight')
            ->fetchAll();

        foreach ($query as &$link) {
            $link[ 'link_edit' ]   = self::router()
                ->generateUrl(
                    'menu.link.edit',
                    [
                        'menuId' => $link[ 'menu_id' ],
                        'linkId' => $link[ 'link_id' ]
                    ]
                );
            $link[ 'link_remove' ] = self::router()
                ->generateUrl(
                    'menu.link.remove.modal',
                    [
                        'menuId' => $link[ 'menu_id' ],
                        'linkId' => $link[ 'link_id' ]
                    ]
                );
            $link[ 'submenu' ]     = $link[ 'has_children' ]
                ? $this->renderMenu($menuId, $link[ 'link_id' ], $level + 1)
                : $this->createBlockMenuShowForm($menuId, null, $level + 1);

            if (!$link[ 'key' ]) {
                continue;
            }

            $link[ 'link' ] = self::menu()->rewiteUri($link[ 'link' ], $link[ 'query' ], $link[ 'fragment' ]);
        }
        unset($link);

        return $this->createBlockMenuShowForm($menuId, $query, $level);
    }

    private function createBlockMenuShowForm(
        int $menuId,
        ?array $query,
        int $level
    ): Block {
        return self::template()
                ->createBlock('menu/content-menu-show_form.php', $this->pathViews)
                ->addNameOverride("menu-show-$menuId.php")
                ->addVars([ 'level' => $level, 'menu' => $query ]);
    }
}
