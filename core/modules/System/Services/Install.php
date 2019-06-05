<?php

namespace SoosyzeCore\System\Services;

use Soosyze\Components\Http\Redirect;

class Install
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function hook404($request, &$response)
    {
        $route    = $this->router->getRoute('install.index');
        $response = new Redirect($route);
    }
}
