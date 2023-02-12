<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\Trumbowyg\Controller\Trumbowyg;

RouteCollection::setNamespace(Trumbowyg::class)->prefix('/api')->group(function (RouteGroup $r): void {
    $r->get('trumbowyg.upload', '/trumbowyg/upload', '@upload');
});
