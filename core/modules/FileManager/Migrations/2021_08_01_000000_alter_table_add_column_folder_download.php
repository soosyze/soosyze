<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('profil_file', function (TableAlter $tableAlter) {
            $tableAlter->boolean('folder_download')->valueDefault(false);
        });
    }
];
