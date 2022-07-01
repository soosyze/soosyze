<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

RouteCollection::setNamespace('Soosyze\Core\Modules\Trumbowyg\Controller\Trumbowyg')->prefix('/api')->group(function (RouteGroup $r): void {
    $r->get('trumbowyg.upload', '/trumbowyg/upload', '@upload');
});
