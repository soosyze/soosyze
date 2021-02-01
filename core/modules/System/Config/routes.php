<?php

use Soosyze\Components\Router\Route as R;

R::useNamespace('SoosyzeCore\System\Controller');

R::get('system.module.edit', 'admin/modules', 'ModulesManager@edit');
R::post('system.module.update', 'admin/modules', 'ModulesManager@update');

R::get('system.theme.index', 'admin/theme', 'Theme@index');
R::get('system.theme.admin', 'admin/theme/:type', 'Theme@admin', [ ':type' => 'admin|public' ]);
R::get('system.theme.active', 'admin/theme/:type/active/:name', 'Theme@active', [ ':type' => 'admin|public', ':name' => '\w+' ]);
R::get('system.theme.edit', 'admin/theme/:type/edit', 'Theme@edit', [ ':type' => 'admin|public' ]);
R::post('system.theme.update', 'admin/theme/:type/edit', 'Theme@update', [ ':type' => 'admin|public' ]);

R::get('system.tool.admin', 'admin/tool', 'Tool@admin');
R::get('system.tool.cron', 'admin/tool/cron', 'Tool@cron');
R::get('system.tool.trans', 'admin/tool/trans', 'Tool@updateTranslations');

R::get('api.route', 'api/route', 'RouteApi@index');
