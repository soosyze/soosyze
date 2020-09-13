<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Config\Controller');

R::get('config.admin', 'admin/config', 'Config@admin');
R::get('config.edit', 'admin/config/:id', 'Config@edit', [ ':id' => '\w+' ]);
R::post('config.update', 'admin/config/:id', 'Config@update', [ ':id' => '\w+' ]);
