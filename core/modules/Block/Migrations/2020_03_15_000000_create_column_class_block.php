<?php

use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch) {
        $sch->alterTable('block', function (TableBuilder $table) {
            $table->string('class')->valueDefault('');
        });
    }
];
