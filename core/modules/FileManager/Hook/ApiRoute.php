<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\FileManager\Hook;

use Soosyze\Components\Router\Router;
use Soosyze\Core\Modules\System\Services\Alias;

class ApiRoute implements \Soosyze\Core\Modules\System\ApiRouteInterface
{
    /**
     * @var Alias
     */
    private $alias;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Alias $alias, Router $router)
    {
        $this->alias  = $alias;
        $this->router = $router;
    }

    public function apiRoute(
        array &$routes,
        string $search,
        string $exclude,
        int $limit
    ): void {
        $title = t('Public file manager');
        if ($title === $exclude || stristr($title, $search) === false) {
            return;
        }

        $routes[] = [
            'link'  => $this->router->generateUrl(
                'filemanager.public',
                [ 'path' => '/download' ]
            ),
            'route' => $this->alias->getAlias(
                'filemanager/public/download',
                'filemanager/public/download'
            ),
            'title' => $title
        ];
    }
}
