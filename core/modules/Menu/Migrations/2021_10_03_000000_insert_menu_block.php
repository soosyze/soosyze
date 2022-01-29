<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        if (!$sch->hasTable('block')) {
            return;
        }

        $req
            ->insertInto('block', [
                'title', 'is_title', 'section', 'hook',
                'weight', 'pages', 'key_block',
                'options',
                'theme'
            ])
            ->values([
                t('Administration menu'), false, 'main_menu', 'menu',
                0, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-admin', 'parent' => -1 ]),
                'admin'
            ])
            ->values([
                t('User Menu'), false, 'second_menu', 'menu',
                1, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-user', 'parent' => -1 ]),
                'admin'
            ])
            ->values([
                t('Main Menu'), false, 'main_menu', 'menu',
                0, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-main', 'parent' => -1 ]),
                'public'
            ])
            ->values([
                t('User Menu'), false, 'second_menu', 'menu',
                1, '', 'menu',
                json_encode([ 'depth' => 10, 'name' => 'menu-user', 'parent' => -1 ]),
                'public'
            ])
            ->execute();

        $req->insertInto('module_require', [ 'title_module', 'title_required', 'version' ])
            ->values(['Menu', 'Block', '1.0.*']);
    }
];
