<?php

namespace SoosyzeCore\Menu\Services;

class Menu
{
    protected $core;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    protected $router;

    protected $config;

    protected $query;

    protected $pathViews;

    public function __construct($alias, $core, $router, $config, $query)
    {
        $this->alias     = $alias;
        $this->core      = $core;
        $this->router    = $router;
        $this->config    = $config;
        $this->query     = $query;
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function getPathViews()
    {
        return $this->pathViews;
    }

    public function find($id)
    {
        return $this->query
                ->from('menu_link')
                ->where('id', '==', $id)
                ->fetch();
    }

    public function getMenu($name)
    {
        return $this->query
                ->from('menu')
                ->where('name', $name);
    }

    public function getAllMenu()
    {
        return $this->query
                ->from('menu')
                ->fetchAll();
    }

    public function getLinkPerMenu($name)
    {
        $menu = $this->getMenu($name)->fetch();

        return $this->query
                ->from('menu_link')
                ->where('menu', '==', $menu[ 'name' ]);
    }

    public function getInfo($link, $request)
    {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            return [
                'key'      => '',
                'link'     => $link,
                'query'    => '',
                'fragment' => '',
            ];
        }

        $isRewrite = $this->router->isRewrite();
        $uri       = \Soosyze\Components\Http\Uri::create(($isRewrite
                ? ''
                : 'q=') . $link);
        if (!$isRewrite) {
            $parse = parse_url("?q=$link");
            parse_str($parse[ 'query' ], $result);

            $uri = $uri->withPath($result[ 'q' ]);
            unset($result[ 'q' ]);
            $uri = $uri->withQuery(http_build_query($result));
        }

        $linkSource = $uri->getPath() === '/'
            ? $this->config->get('settings.path_index', '/')
            : $uri->getPath();
        $linkSource = $this->alias->getSource($linkSource, $linkSource);

        $uriSource = $isRewrite
            ? $uri->withPath($linkSource)
            : $uri->withQuery('q=' . $linkSource);

        $route = $this->router->parse($request->withUri($uriSource)->withMethod('get'));

        return [
            'key'      => isset($route[ 'key' ])
            ? $route[ 'key' ]
            : '',
            'link'     => $uri->getPath(),
            'query'    => $uri->getQuery(),
            'fragment' => $uri->getFragment(),
        ];
    }

    public function renderMenu($nameMenu, $parent = -1, $level = 1)
    {
        $query = $this->query
            ->from('menu_link')
            ->where('active', '==', 1)
            ->where('menu', $nameMenu)
            ->where('parent', '==', $parent)
            ->orderBy('weight')
            ->fetchAll();

        if (empty($query)) {
            return null;
        }

        foreach ($query as &$menu) {
            $menu[ 'title_link' ] = t($menu[ 'title_link' ]);
            $menu[ 'submenu' ]    = $this->renderMenu($nameMenu, $menu[ 'id' ], $level + 1);
        }

        return $this->core
                ->get('template')
                ->createBlock('menu.php', $this->pathViews)
                ->nameOverride($nameMenu . '.php')
                ->addVars([
                    'menu' => $this->getGrantedLink($query, $this->core->getRequest()),
                    'level' => $level
                ]);
    }

    public function rewiteUri($link)
    {
        $basePath = $this->core->getRequest()->getBasePath();
        $uri      = \Soosyze\Components\Http\Uri::create($basePath)
            ->withFragment($link[ 'fragment' ]);

        if ($this->router->isRewrite()) {
            return $uri->withPath($link[ 'link' ])
                    ->withQuery($link[ 'query' ]);
        }

        $path = $link[ 'link' ] === '/'
            ? ''
            : 'q=' . $link[ 'link' ];

        $query = $path && $link[ 'query' ]
            ? '&' . $link[ 'query' ]
            : $link[ 'query' ];

        return $uri->withQuery($path . $query);
    }

    /**
     * Retire les liens restreins dans un menu et définit le lien courant.
     *
     * @param array   $query   liens du menu
     * @param Request $request
     *
     * @return array
     */
    protected function getGrantedLink($query, $request)
    {
        $route = $this->router->parseQueryFromRequest();

        foreach ($query as $key => &$menu) {
            if (!$menu[ 'key' ]) {
                $menu[ 'link_active' ] = '';

                continue;
            }

            if ($alias = $this->alias->getAlias($menu[ 'link' ])) {
                $menu[ 'link' ] = $alias;
            }

            $link = $request->withUri(
                $this->router->isRewrite()
                ? $request->getUri()->withPath($menu[ 'link' ])
                : $request->getUri()->withQuery('q=' . $menu[ 'link' ])
            );

            /* Test avec un hook si le menu doit-être affiché à partir du lien du menu. */
            if (!$this->core->callHook('app.granted.route', [ $link ])) {
                unset($query[ $key ]);

                continue;
            }

            $menu[ 'link_active' ] = 0 === strpos($route, $menu[ 'link' ])
                ? 'active'
                : '';
            $menu[ 'link' ]        = $this->rewiteUri($menu);
        }

        return $query;
    }
}
