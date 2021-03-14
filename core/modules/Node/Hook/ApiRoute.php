<?php

namespace SoosyzeCore\Node\Hook;

class ApiRoute implements \SoosyzeCore\System\ApiRouteInterface
{
    /**
     * @var \SoosyzeCore\System\Services\Alias
     */
    private $alias;

    /**
     * @var \Queryflatfile\Request
     */
    private $query;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($alias, $query, $router)
    {
        $this->alias  = $alias;
        $this->query  = $query;
        $this->router = $router;
    }

    public function apiRoute(array &$routes, $search, $exclude, $limit)
    {
        $nodes = $this->query
            ->from('node')
            ->where('title', '!=', $exclude)
            ->where('title', 'ilike', "%$search%")
            ->limit($limit)
            ->fetchAll();

        foreach ($nodes as $node) {
            $alias = $this->alias->getAlias("node/{$node[ 'id' ]}", "node/{$node[ 'id' ]}");

            $routes[] = [
                'link'  => $this->router->makeRoute($alias),
                'route' => $alias,
                'title' => $node[ 'title' ]
            ];
        }
    }
}
