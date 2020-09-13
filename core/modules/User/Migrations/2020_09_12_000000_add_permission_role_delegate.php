<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        $req->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'role.all' ])
            ->execute();
    }
];
