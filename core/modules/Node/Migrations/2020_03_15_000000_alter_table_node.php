<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('node', function (TableAlter $ta) {
            $ta->renameColumn('created', 'date_created');
            $ta->integer('entity_id')->nullable();
            $ta->renameColumn('changed', 'date_changed');
            $ta->string('meta_description')->valueDefault('');
            $ta->renameColumn('noarchive', 'meta_noarchive');
            $ta->renameColumn('nofollow', 'meta_nofollow');
            $ta->renameColumn('noindex', 'meta_noindex');
            $ta->renameColumn('noindex', 'meta_noindex');
            $ta->string('meta_title')->valueDefault('');
            $ta->string('type', 32)->modify();
        });
    }
];
