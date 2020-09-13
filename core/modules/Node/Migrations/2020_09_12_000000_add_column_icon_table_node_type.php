<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('node_type', function (TableBuilder $table) {
            $table->string('node_type_icon');
        });
    }
];
