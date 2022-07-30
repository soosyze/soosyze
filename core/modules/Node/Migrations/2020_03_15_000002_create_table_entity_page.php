<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->createTableIfNotExists('entity_page', function (TableBuilder $tb) {
            $tb->increments('page_id');
            $tb->text('body');
        });

        $nodes = $req->from('node')->where('type', '=', 'page')->fetchAll();
        /** @phpstan-var array{ id: int, field: string } $node */
        foreach ($nodes as $i => $node) {
            $field = unserialize($node[ 'field' ]);
            if (!is_array($field)) {
                continue;
            }

            $req->insertInto('entity_page', [ 'page_id', 'body' ])
                ->values([ $i,
                    empty($field[ 'body' ]) || !is_string($field[ 'body' ])
                        ? 'content'
                        : $field[ 'body' ]
                    ])
                ->execute();

            $req->update('node', [
                    'entity_id' => $i
                ])
                ->where('id', '=', $node[ 'id' ])
                ->execute();
        }
    }
];
