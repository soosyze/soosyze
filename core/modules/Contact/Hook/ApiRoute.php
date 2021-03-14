<?php

namespace SoosyzeCore\Contact\Hook;

class ApiRoute implements \SoosyzeCore\System\ApiRouteInterface
{
    /**
     * @var \SoosyzeCore\System\Services\Alias
     */
    private $alias;

    /**
     * @var \Soosyze\Config
     */
    private $config;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($alias, $config, $router)
    {
        $this->alias  = $alias;
        $this->config = $config;
        $this->router = $router;
    }

    public function apiRoute(array &$routes, $search, $exclude, $limit)
    {
        $title = t('Contact');
        if ($title === $exclude) {
            return;
        }
        if (stristr($title, $search) === false) {
            return;
        }

        $routes[] = [
            'link'  => $this->router->getRoute('contact'),
            'route' => $this->alias->getAlias('contact', 'contact'),
            'title' => $title
        ];
    }
}
