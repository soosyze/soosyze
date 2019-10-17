<?php

use Soosyze\Route as R;

R::useNamespace('SoosyzeCore\System\Controller');

R::get('install.index', '/', 'Install@index');
R::get('install.step', 'install/step/:id', 'Install@step', [':id' => '\w+']);
R::post('install.language', 'install/language/:id', 'Install@language', [':id' => '\w+']);
R::post('install.step.check', 'install/step/:id', 'Install@stepCheck', [':id' => '\w+']);
