<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Hook;

use Core;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SoosyzeCore\Template\Services\Templating;

class App
{
    /**
     * @var Core
     */
    private $core;

    public function __construct(Core $core)
    {
        $this->core = $core;
    }

    public function hookMenuShowResponseAfter(RequestInterface $request, ResponseInterface &$response): void
    {
        if (!($response instanceof Templating)) {
            return;
        }

        $vendor = $this->core->getPath('modules', 'modules/core', false);

        $response->addScript('menu', "$vendor/Menu/Assets/js/menu.js");
    }
}
