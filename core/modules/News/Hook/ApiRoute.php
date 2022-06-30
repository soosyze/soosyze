<?php

declare(strict_types=1);

namespace SoosyzeCore\News\Hook;

use Soosyze\Components\Router\Router;
use SoosyzeCore\News\Hook\Config;
use SoosyzeCore\System\Services\Alias;

class ApiRoute implements \SoosyzeCore\System\ApiRouteInterface
{
    /**
     * @var Alias
     */
    private $alias;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $newTitle;

    public function __construct(
        Alias $alias,
        Router $router,
        string $newTitle = Config::TITLE
    ) {
        $this->alias    = $alias;
        $this->router   = $router;
        $this->newTitle = $newTitle;
    }

    public function apiRoute(
        array &$routes,
        string $search,
        string $exclude,
        int $limit
    ): void {
        $titleI18n = t($this->newTitle);
        if ($titleI18n === $exclude || stristr($titleI18n, $search) === false) {
            return;
        }

        $routes[] = [
            'link'  => $this->router->generateUrl('news.index'),
            'route' => $this->alias->getAlias('news', 'news'),
            'title' => $titleI18n
        ];
    }
}
