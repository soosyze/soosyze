<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('node', function (TableBuilder $table) {
            $table->integer('user_id')->nullable();
        });

        $req->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 2, 'node.show.own' ])
            ->values([ 3, 'node.user.edit' ])
            ->values([ 2, 'node.cloned.own' ])
            ->values([ 2, 'node.edited.own' ])
            ->values([ 2, 'node.deleted.own' ])
            ->execute();
    }
];
