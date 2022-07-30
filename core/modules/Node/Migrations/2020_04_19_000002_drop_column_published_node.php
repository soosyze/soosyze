<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('node', function (TableAlter $ta) {
            $ta->dropColumn('published');
        });
    }
];
