<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('menu_link', function (TableBuilder $table) {
            $table->boolean('target_link')->valueDefault(false)->modify();
        });
    }
];
