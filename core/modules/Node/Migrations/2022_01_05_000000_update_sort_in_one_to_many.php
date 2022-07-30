<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        $nodeTypes = $req
            ->from('node_type_field')
            ->leftJoin('field', 'field_id', '=', 'field.field_id')
            ->where('field_type', '=', 'one_to_many')
            ->fetchAll();

        /** @phpstan-var array{ field_option: string, field_id: int, node_type: string } $type */
        foreach ($nodeTypes as $type) {
            /** @phpstan-var array{ sort: int|string } $options */
            $options = (array) json_decode($type[ 'field_option' ], true);

            if ($options[ 'sort' ] === 'weight') {
                $options[ 'sort' ] = SORT_ASC;
            }

            $req->update('node_type_field', [
                    'field_option' => json_encode($options)
                ])
                ->where('field_id', '=', $type[ 'field_id' ])
                ->where('node_type', '=', $type[ 'node_type' ])
                ->execute();
        }
    }
];
