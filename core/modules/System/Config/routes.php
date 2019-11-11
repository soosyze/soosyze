<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\System\Controller');

R::get('system.module.edit', 'admin/modules', 'ModulesManager@edit');
R::post('system.module.update', 'admin/modules', 'ModulesManager@update');
