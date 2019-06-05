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
        return $this->query->from('menu')->where('name', $name);
    }

    public function getLinkPerMenu($name)
    {
        return $this->getMenu($name)
                ->leftJoin('menu_link', 'name', 'menu_link.menu')
                ->isNotNull('id');
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

            $uri    = $request->getUri()->withQuery($query);
            $output = $this->router->parse($request->withUri($uri));
        }

        return $output;
    }
}
