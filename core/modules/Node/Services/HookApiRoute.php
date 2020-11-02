<?php

namespace SoosyzeCore\Node\Services;

class HookApiRoute
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

    public function hookApiRoute(array &$routes, $search, $exclude)
    {
        $nodes = $this->query->from('node')->where('title', 'ilike', "%$search%")->limit(5)->fetchAll();

        foreach ($nodes as $node) {
            $alias    = $this->alias->getAlias('node/' . $node[ 'id' ], 'node/' . $node[ 'id' ]);
            if ($alias === $exclude) {
                continue;
            }
            $routes[] = [
                'title' => $node[ 'title' ],
                'route' => $alias,
                'link'  => $this->router->makeRoute($alias)
            ];
        }
    }
}
