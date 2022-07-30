<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('node', function (TableBuilder $table) {
            $table->boolean('sticky')->valueDefault(false);
        });
    }
];
