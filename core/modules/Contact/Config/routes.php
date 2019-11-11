<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Contact\Controller');

R::get('contact', 'contact', 'Contact@form');
R::post('contact.check', 'contact', 'Contact@formCheck');
