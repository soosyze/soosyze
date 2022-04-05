<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('node', function (TableAlter $ta) {
            $ta->dropColumn('field');
        });
    }
];
