<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Dashboard\Controller');

R::get('dashboard.index', 'admin/dashboard', 'Dashboard@index');
R::get('dashboard.about', 'admin/dashboard/about', 'Dashboard@about');
R::get('dashboard.cron', 'admin/dashboard/cron', 'Dashboard@cron');
