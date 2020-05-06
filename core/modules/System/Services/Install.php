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
        $response = new Redirect($this->router->getRoute('install.index'));
    }
}
