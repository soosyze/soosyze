<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Contact\Hook;

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
        $title = t('Contact');
        if ($title === $exclude || stristr($title, $search) === false) {
            return;
        }

        $routes[] = [
            'link'  => $this->router->generateUrl('contact.form'),
            'route' => $this->alias->getAlias('contact', 'contact'),
            'title' => $title
        ];
    }
}
