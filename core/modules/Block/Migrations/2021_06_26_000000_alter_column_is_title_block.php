<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('block', function (TableAlter $table) {
            $table->boolean('is_title')->valueDefault(true);
        });
    }
];
