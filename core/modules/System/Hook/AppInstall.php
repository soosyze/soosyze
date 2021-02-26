<?php

namespace SoosyzeCore\System\Hook;

use Soosyze\Components\Http\Redirect;

class AppInstall
{
    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function hook404($request, &$response)
    {
        $response = new Redirect($this->router->getRoute('install.index'));
    }
}
