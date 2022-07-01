<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Menu\Hook;

use Core;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soosyze\Core\Modules\Template\Services\Templating;

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

    public function hookMenuShowResponseAfter(
        RequestInterface $request,
        ResponseInterface &$response
    ): void {
        if (!($response instanceof Templating)) {
            return;
        }

        $vendor = $this->core->getPath('modules', 'modules/core', false);

        $response->addScript('menu', "$vendor/Menu/Assets/js/menu.js");
    }
}
