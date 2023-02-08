<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\System\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @phpstan-import-type RouteApiEntity from \Soosyze\Core\Modules\System\ApiRouteInterface
 */
class RouteApi extends \Soosyze\Controller
{
    private const LIMIT_ROUTE = 5;

    public function index(ServerRequestInterface $req): ResponseInterface
    {
        $get = $req->getQueryParams();

        $search  = $get[ 'title' ] ?? '';
        $exclude = $get[ 'exclude' ] ?? '';
        $limit   = $get[ 'limit' ] ?? self::LIMIT_ROUTE;

        /** @phpstan-var array<RouteApiEntity> $routes */
        $routes = [];
        $this->container->callHook('api.route', [ &$routes, $search, $exclude, $limit ]);

        if ($routes === []) {
            $routes[] = [
                'title' => t('No results found'),
                'route' => '',
                'link'  => '#'
            ];
        }

        usort($routes, static function (array $a, array $b) use ($search): int {
            return stripos($a[ 'title' ], $search) <=> stripos($b[ 'title' ], $search);
        });

        return $this->json(
            200,
            array_slice($routes, 0, self::LIMIT_ROUTE)
        );
    }
}
