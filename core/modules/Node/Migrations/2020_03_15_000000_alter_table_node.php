<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('node', function (TableBuilder $table) {
            $table->renameColumn('created', 'date_created')
                    ->integer('entity_id')->nullable()
                    ->renameColumn('changed', 'date_changed')
                    ->string('meta_description')->valueDefault('')
                    ->renameColumn('noarchive', 'meta_noarchive')
                    ->renameColumn('nofollow', 'meta_nofollow')
                    ->renameColumn('noindex', 'meta_noindex')
                    ->renameColumn('noindex', 'meta_noindex')
                    ->string('meta_title')->valueDefault('')
                    ->string('type', 32)->modify();
        });
    }
];
