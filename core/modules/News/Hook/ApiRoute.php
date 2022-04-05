<?php

declare(strict_types=1);

namespace SoosyzeCore\News\Hook;

use Soosyze\Components\Router\Router;
use Soosyze\Config;
use SoosyzeCore\System\Services\Alias;

class ApiRoute implements \SoosyzeCore\System\ApiRouteInterface
{
    /**
     * @var Alias
     */
    private $alias;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Alias $alias, Config $config, Router $router)
    {
        $this->alias  = $alias;
        $this->config = $config;
        $this->router = $router;
    }

    public function apiRoute(array &$routes, string $search, string $exclude, int $limit): void
    {
        /** @phpstan-var string $title */
        $title = $this->config->get('settings.new_title', 'Articles');

        $titleI18n = t($title);
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
