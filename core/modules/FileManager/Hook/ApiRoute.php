<?php

namespace SoosyzeCore\FileManager\Hook;

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
        $title = t('Public file manager');
        if ($title === $exclude) {
            return;
        }
        if (stristr($title, $search) === false) {
            return;
        }

        $routes[] = [
            'link'  => $this->router->getRoute(
                'filemanager.public',
                [ ':path' => '/download' ]
            ),
            'route' => $this->alias->getAlias(
                'filemanager/public/download',
                'filemanager/public/download'
            ),
            'title' => $title
        ];
    }
}
