<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\Contact\Controller\Contact;

RouteCollection::setNamespace(Contact::class)->name('contact.')->prefix('/contact')->group(function (RouteGroup $r): void {
    $r->get('form', '/', '@form');
    $r->post('check', '/', '@formCheck');
});
