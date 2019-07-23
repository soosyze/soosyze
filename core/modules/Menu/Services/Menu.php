<?php

namespace SoosyzeCore\Menu\Services;

use Soosyze\Components\Validator\Validator;

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
        $output = (new Validator())
            ->setRules([ 'link' => 'required|url' ])
            ->setInputs([ 'link' => $link ])
            ->isValid();

        if (!$output) {
            $query = $link === '/'
                ? $this->config->get('settings.path_index', '/')
                : $link;
            
            $parse = parse_url("?q=$query");
            $uri = $request->getUri();
            if (!empty($parse['query'])) {
                $uri = $uri->withQuery($parse['query']);
            } elseif (!empty($parse['fragment'])) {
                $uri = $uri->withFragment($parse['fragment']);
            }
            $output = $this->router->parse($request->withUri($uri));
        }

        return $output;
    }
}
