<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->createTableIfNotExists('entity_page_private', function (TableBuilder $table) {
            $table->increments('page_private_id')
                    ->text('body');
        });
        $req->insertInto('node_type', [
                'node_type',
                'node_type_name',
                'node_type_description',
                'node_type_icon'
            ])
            ->values([
                'page_private',
                'Private page',
                'Use the private pages for content reserved for your members.',
                'far fa-file'
            ])
            ->execute();

        $req->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_label', 'field_weight', 'field_rules'
            ])
            ->values([ 'page_private', 1, 'Body', 2, '!required|string' ])
            ->execute();
    }
];
