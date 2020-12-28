<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\Dashboard\Controller');

R::get('dashboard.index', 'admin/dashboard', 'Dashboard@index');
R::get('dashboard.info', 'admin/dashboard/info', 'Dashboard@info');
R::get('dashboard.cron', 'admin/dashboard/cron', 'Dashboard@cron');
R::get('dashboard.trans', 'admin/dashboard/trans', 'Dashboard@updateTranslations');
