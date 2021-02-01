<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Dashboard\Controller');

R::get('dashboard.index', 'admin/dashboard', 'Dashboard@index');
R::get('dashboard.info', 'admin/dashboard/info', 'Dashboard@info');
