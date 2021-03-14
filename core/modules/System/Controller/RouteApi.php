<?php

namespace SoosyzeCore\System\Controller;

class RouteApi extends \Soosyze\Controller
{
    const LIMIT_ROUTE = 5;

    public function index($req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $get = $req->getQueryParams();

        $search = empty($get[ 'title' ])
            ? ''
            : $get[ 'title' ];

        $exclude = empty($get[ 'exclude' ])
            ? ''
            : $get[ 'exclude' ];

        $limit = empty($get[ 'limit' ])
            ? self::LIMIT_ROUTE
            : $get[ 'limit' ];

        $routes = [];
        $this->container->callHook('api.route', [ &$routes, $search, $exclude, $limit ]);

        if (empty($routes)) {
            $routes[] = [
                'title' => t('No results found'),
                'route' => '',
                'link'  => '#'
            ];
        }

        usort($routes, static function ($a, $b) use ($search) {
            $aPos = stripos($a[ 'title' ], $search);
            $bPos = stripos($b[ 'title' ], $search);

            if ($aPos == $bPos) {
                return 0;
            }

            return ($aPos < $bPos)
                ? -1
                : 1;
        });

        return $this->json(
            200,
            array_slice($routes, 0, self::LIMIT_ROUTE)
        );
    }
}
