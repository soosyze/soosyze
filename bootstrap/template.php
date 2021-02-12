<?php

use SoosyzeCore\Template\Services\Templating;

$vendor = \Core::getInstance()->getPath('vendor_public', 'public/vendor', false);

Templating::setScriptsGlobal([
    'jquery'   => [
        'src' => " $vendor/jquery/jquery-3.5.1.min.js"
    ],
    'sortable' => [
        'src' => "$vendor/Sortable/Sortable.min.js"
    ],
    'select2'  => [
        'src' => "$vendor/select2/select2.min.js"
    ],
    'soosyze'  => [
        'src' => "$vendor/soosyze/soosyze.js"
    ]
]);
Templating::setStylesGlobal([
    'fontawesome' => [
        'href' => "$vendor/fontawesome/css/all.min.css",
        'rel'  => 'stylesheet'
    ],
    'select2'     => [
        'href' => "$vendor/select2/select2.min.css",
        'rel'  => 'stylesheet'
    ],
    'soosyze'     => [
        'href' => "$vendor/soosyze/soosyze.css",
        'rel'  => 'stylesheet'
    ]
]);
