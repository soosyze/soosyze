<?php

namespace SoosyzeCore\Menu\Services;

class Menu
{
    protected $router;

    protected $config;

    protected $query;

    public function __construct($router, $config, $query)
    {
        $this->router = $router;
        $this->config = $config;
        $this->query  = $query;
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

    public function getLinkPerMenu($name)
    {
        $menu = $this->getMenu($name)->fetch();

        return $this->query
                ->from('menu_link')
                ->where('menu', '==', $menu['name']);
    }

    public function isUrlOrRoute($link, $request)
    {
        if (!isset($link[ 'link' ])) {
            return false;
        }
        /* Met en forme les données d'un lien s'il est une URL. */
        if (filter_var($link[ 'link' ], FILTER_VALIDATE_URL)) {
            $uri = \Soosyze\Components\Http\Uri::create($link[ 'link' ]);

            return [
                'key'       => '',
                'link'      => (string) $uri,
                'fragment' => '',
            ];
        }
        /* Prépare le lien si celui-ci est l'index. */
        $isIndex = $link[ 'link' ] === '/' || strpos($link[ 'link' ], '/#') === 0;
        $query = $isIndex
            ? $this->config->get('settings.path_index', '/') . str_replace('/', '', $link[ 'link' ])
            : $link[ 'link' ];

        $parse = parse_url("?q=$query");
        $uri   = $request->getUri();
        if (!empty($parse[ 'query' ])) {
            $uri = $uri->withQuery($parse[ 'query' ]);
        }
        if (!empty($parse[ 'fragment' ])) {
            $uri = $uri->withFragment($parse[ 'fragment' ]);
        }

        if ($route = $this->router->parse($request->withUri($uri))) {
            return [
                'key'       => $route['key'],
                'link'      => $isIndex ? '/' : str_replace('q=', '', $uri->getQuery()),
                'fragment' => $uri->getFragment(),
            ];
        }

        return false;
    }
}
