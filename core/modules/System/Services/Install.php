<?php

namespace SoosyzeCore\System\Services;

use Soosyze\Components\Http\Redirect;

class Install
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
