<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        if (!$sch->hasTable('menu')) {
            return;
        }
        $req
            ->insertInto('menu_link', [
                'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                'system.tool.admin', 'fa fa-tools', 'Tool', 'admin/tool',
                'menu-admin', 7, -1
            ])
            ->execute();

        $req->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'system.tool.manage' ])
            ->execute();
    }
];
