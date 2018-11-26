<?php

namespace Install\Services;

use Soosyze\Components\Http\Redirect;

class Install
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function hook404($request, &$reponse)
    {
        $route   = $this->router->getRoute('install.index');
        $reponse = new Redirect($route);
    }
}
