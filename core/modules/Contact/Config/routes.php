<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

RouteCollection::setNamespace('SoosyzeCore\Contact\Controller\Contact')->name('contact.')->prefix('/contact')->group(function (RouteGroup $r): void {
    $r->get('form', '/', '@form');
    $r->post('check', '/', '@formCheck');
});
