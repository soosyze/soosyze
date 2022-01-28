<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

RouteCollection::setNamespace('SoosyzeCore\News\Controller\News')->name('news.')->prefix('/news')->group(function (RouteGroup $r): void {
    $r->get('index', '', '@index');
    $r->get('page', '/page/:id', '@page', [ ':id' => '[1-9]\d*' ]);

    $r->prefix('/:year')->withs([ ':year' => '\d{4}' ])->group(function (RouteGroup $r): void {
        $r->get('years', ':id', '@viewYears', [ ':id' => '(/page/[1-9]\d*)?' ]);
        $r->get('years.page', '/page/:id', '@viewYears', [ ':id' => '[1-9]\d*' ]);

        $r->prefix('/:month')->withs([ ':month' => '0[1-9]|1[0-2]' ])->group(function (RouteGroup $r): void {
            $r->get('month', ':id', '@viewMonth', [ ':id' => '(/page/[1-9]\d*)?' ]);
            $r->get('month.page', '/page/:id', '@viewMonth', [ ':id' => '[1-9]\d*' ]);

            $r->prefix('/:day')->withs([ ':day' => '[0-2][1-9]|3[0-1]' ])->group(function (RouteGroup $r): void {
                $r->get('day', ':id', '@viewDay', [ ':id' => '(/page/[1-9]\d*)?' ]);
                $r->get('day.page', '/page/:id', 'News@viewDay', [ ':id' => '[1-9]\d*' ]);
            });
        });
    });
    $r->get('rss', '/feed/rss', '@viewRss');
});
