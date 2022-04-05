<?php

declare(strict_types=1);

namespace SoosyzeCore\System\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @phpstan-import-type RouteApiEntity from \SoosyzeCore\System\ApiRouteInterface
 */
class RouteApi extends \Soosyze\Controller
{
    const LIMIT_ROUTE = 5;

    public function index(ServerRequestInterface $req): ResponseInterface
    {
        $get = $req->getQueryParams();

        $search  = $get[ 'title' ] ?? '';
        $exclude = $get[ 'exclude' ] ?? '';
        $limit   = $get[ 'limit' ] ?? self::LIMIT_ROUTE;

        /** @phpstan-var array<RouteApiEntity> $routes */
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
            return stripos($a[ 'title' ], $search) <=> stripos($b[ 'title' ], $search);
        });

        return $this->json(
            200,
            array_slice($routes, 0, self::LIMIT_ROUTE)
        );
    }
}
