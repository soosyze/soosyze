<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('system_alias_url', function (TableAlter $table) {
            $table->increments('id');
        });
    }
];
