<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Services;

use Core;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Queryflatfile\RequestInterface as QueryInterface;
use Soosyze\Components\Http\Uri;
use Soosyze\Components\Router\Route;
use Soosyze\Components\Router\Router;
use SoosyzeCore\QueryBuilder\Services\Query;
use SoosyzeCore\System\Services\Alias;
use SoosyzeCore\Template\Services\Block;
use SoosyzeCore\Template\Services\Templating;

/**
 * @phpstan-import-type MenuEntity from \SoosyzeCore\Menu\Extend
 * @phpstan-import-type MenuLinkEntity from \SoosyzeCore\Menu\Extend
 *
 * @phpstan-type Submenu array<
 *      array{
 *          key: string,
 *          request: \Psr\Http\Message\RequestInterface,
 *          title_link: string
 *      }
 *  >
 */
class Menu
{
    /**
     * @var Alias
     */
    private $alias;

    /**
     * @var Core
     */
    private $core;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Templating
     */
    private $templating;

    public function __construct(
        Alias $alias,
        Core $core,
        Query $query,
        Router $router,
        Templating $templating
    ) {
        $this->alias      = $alias;
        $this->core       = $core;
        $this->query      = $query;
        $this->router     = $router;
        $this->templating = $templating;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function getPathViews(): string
    {
        return $this->pathViews;
    }

    public function find(int $linkId): ?array
    {
        return $this->query
                ->from('menu_link')
                ->where('link_id', '=', $linkId)
                ->fetch();
    }

    public function deleteLinks(callable $callable): void
    {
        $links = $callable();

        foreach ($links as $link) {
            $this->query
                ->from('menu_link')
                ->delete()
                ->where('link_id', '=', $link[ 'link_id' ])
                ->execute();
            $this->query
                ->update('menu_link', [ 'parent' => $link[ 'parent' ] ])
                ->where('parent', '=', $link[ 'link_id' ])
                ->execute();
        }
    }

    public function getMenu(int $menuId): QueryInterface
    {
        return $this->query
                ->from('menu')
                ->where('menu_id', '=', $menuId);
    }

    public function getAllMenu(): array
    {
        return $this->query
                ->from('menu')
                ->fetchAll();
    }

    public function getLinkPerMenu(int $menuId): QueryInterface
    {
        return $this->query
                ->from('menu_link')
                ->where('menu_id', '=', $menuId);
    }

    public function getInfo(string $link, RequestInterface $request): array
    {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            return [
                'fragment'    => '',
                'key'         => '',
                'link'        => $link,
                'link_router' => null,
                'query'       => ''
            ];
        }

        $uri = Uri::create($link);

        /** @phpstan-var string $linkSource */
        $linkSource = $this->alias->getSource($uri->getPath(), $uri->getPath());

        $route = $this->router->parse(
            $request
                ->withUri($uri->withPath('/' . ltrim($linkSource, '/')))
                ->withMethod('get')
                ->withoutHeader('x-http-method-override')
        );

        return [
            'key'         => $route instanceof Route ? $route->getKey() : null,
            'link'        => $uri->getPath(),
            'link_router' => $route !== null && $linkSource !== $uri->getPath()
                ? $linkSource
                : null,
            'query'       => $uri->getQuery(),
            'fragment'    => $uri->getFragment(),
        ];
    }

    public function renderMenuSelect(
        int $menuId,
        int $parent = -1,
        int $level = 1
    ): array {
        /** @phpstan-var array<MenuLinkEntity> $query */
        $query = $this->query
            ->from('menu_link')
            ->where('active', '=', true)
            ->where('menu_id', '=', $menuId)
            ->where('parent', '=', $parent)
            ->orderBy('weight')
            ->fetchAll();

        if (empty($query)) {
            return [];
        }

        $options = $level === 1
            ? [ [ 'label' => '« ' . t('Root') . ' »', 'value' => -1 ] ]
            : [];

        $space = str_repeat('│··· ', $level - 1);
        $count = count($query) - 1;

        foreach ($query as $key => $link) {
            $seperator = $count === $key
                ? '└─ '
                : '├─ ';

            $options[] = [
                'label' => $space . $seperator . t($link[ 'title_link' ]),
                'value' => $link[ 'link_id' ]
            ];

            $options = array_merge(
                $options,
                ($link[ 'has_children' ]
                    ? $this->renderMenuSelect($link['menu_id'], $link[ 'link_id' ], $level + 1)
                    : [])
            );
        }

        return $options;
    }

    public function renderMenu(
        int $menuId,
        int $parent = -1,
        int $depth = 10,
        int $level = 1
    ): ?Block {
        /** @phpstan-var array<MenuLinkEntity> $query */
        $query = $this->query
            ->from('menu_link')
            ->where('active', '=', true)
            ->where('menu_id', '=', $menuId)
            ->where('parent', '=', $parent)
            ->orderBy('weight')
            ->fetchAll();

        if (empty($query)) {
            return null;
        }

        foreach ($query as &$link) {
            $link[ 'title_link' ] = t($link[ 'title_link' ]);
            $link[ 'submenu' ]    = $link[ 'has_children' ] && $depth >= $level
                ? $this->renderMenu($menuId, $link[ 'link_id' ], $depth, $level + 1)
                : null;
        }
        unset($link);

        return $this->templating
                ->createBlock('menu.php', $this->pathViews)
                ->addNameOverride("menu-$menuId.php")
                ->addVars([
                    'level' => $level,
                    'menu'  => $this->getGrantedLink($query)
        ]);
    }

    public function rewiteUri(string $link, ?string $query, ?string $fragment): UriInterface
    {
        $basePath = rtrim($this->core->getRequest()->getBasePath(), '/') . '/' . trim($link, '//');

        return Uri::create($basePath)
                ->withQuery($query ?? '')
                ->withFragment($fragment ?? '');
    }

    public function getMenuSubmenu(string $keyRoute, int $menuId): array
    {
        /** @phpstan-var Submenu $menu */
        $menu = [
            [
                'key'        => 'menu.show',
                'request'    => $this->router->generateRequest('menu.show', [
                    'menuId' => $menuId
                ]),
                'title_link' => 'View'
            ], [
                'key'        => 'menu.edit',
                'request'    => $this->router->generateRequest('menu.edit', [
                    'menuId' => $menuId
                ]),
                'title_link' => 'Edit'
            ], [
                'key'        => 'menu.remove',
                'request'    => $this->router->generateRequest('menu.remove', [
                    'menuId' => $menuId
                ]),
                'title_link' => 'Delete'
            ]
        ];

        $this->core->callHook('menu.submenu', [ &$menu ]);

        foreach ($menu as $key => &$link) {
            if (!$this->core->callHook('app.granted.request', [ $link[ 'request' ] ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = $link[ 'request' ]->getUri();
        }

        return [
            'key_route' => $keyRoute,
            'menu'      => $menu
        ];
    }

    public function getMenuLinkSubmenu(
        string $keyRoute,
        int $menuId,
        int $linkId
    ): array {
        /** @phpstan-var Submenu $menu */
        $menu = [
            [
                'key'        => 'menu.link.edit',
                'request'    => $this->router->generateRequest('menu.link.edit', [
                    'menuId' => $menuId, 'linkId' => $linkId
                ]),
                'title_link' => 'Edit'
            ], [
                'key'        => 'menu.link.remove',
                'request'    => $this->router->generateRequest('menu.link.remove', [
                    'menuId' => $menuId, 'linkId' => $linkId
                ]),
                'title_link' => 'Delete'
            ]
        ];

        $this->core->callHook('menu.link.submenu', [ &$menu ]);

        foreach ($menu as $key => &$link) {
            if (!$this->core->callHook('app.granted.request', [ $link[ 'request' ] ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = $link[ 'request' ]->getUri();
        }

        return [
            'key_route' => $keyRoute,
            'menu'      => $menu
        ];
    }

    /**
     * Retire les liens restreins dans un menu et définit le lien courant.
     *
     * @param array $query liens du menu
     *
     * @return array
     */
    private function getGrantedLink(array $query): array
    {
        $route   = $this->router->getPathFromRequest();
        $request = $this->core->getRequest()->withMethod('GET');

        foreach ($query as $key => &$menu) {
            if (!$menu[ 'key' ]) {
                $menu[ 'link_active' ] = '';

                continue;
            }

            $link = $request->withUri(
                $this->rewiteUri(
                    $menu[ 'link_router' ] ?? $menu[ 'link' ],
                    $menu['query'],
                    $menu['fragment']
                )
            );

            /* Test avec un hook si le menu doit-être affiché à partir du lien du menu. */
            if (!$this->core->callHook('app.granted.request', [ $link ])) {
                unset($query[ $key ]);

                continue;
            }

            $menu[ 'link_active' ] = $route === '/' . trim($menu[ 'link' ], '/')
                ? 'active'
                : '';

            $menu[ 'link' ] = $this->rewiteUri($menu[ 'link' ], $menu[ 'query' ], $menu[ 'fragment' ]);
        }
        unset($menu);

        return $query;
    }
}
