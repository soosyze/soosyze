<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('block', function (TableAlter $table) {
            $table->text('options')->nullable()->modify();
        });
    }
];
