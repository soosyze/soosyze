<?php

namespace SoosyzeCore\System\Controller;

class RouteApi extends \Soosyze\Controller
{
    public function index($req)
    {
        $get = $req->getQueryParams();
        
        $search = empty($get[ 'title' ])
            ? ''
            : $get[ 'title' ];
        
        $exclude = empty($get[ 'exclude' ])
            ? ''
            : $get[ 'exclude' ];

        $routes = [];
        $this->container->callHook('api.route', [ &$routes, $search, $exclude ]);

        if (empty($routes)) {
            $routes[] = [
                'title' => t('No results found'),
                'route' => '',
                'link'  => '#'
            ];
        }

        return $this->json(200, $routes);
    }
}
