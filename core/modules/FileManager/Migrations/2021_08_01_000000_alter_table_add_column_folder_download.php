<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('profil_file', function (TableAlter $tableAlter) {
            $tableAlter->boolean('folder_download')->valueDefault(false);
        });
    }
];
