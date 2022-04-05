<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        if (!$sch->hasTable('block')) {
            return;
        }

        /**
         * @phpstan-var array<
         *      array{
         *          block_id: int,
         *          options: string
         *      }
         * > $blockMenus
         */
        $blockMenus = $req->from('block')->where('hook', '=', 'menu')->fetchAll();

        foreach ($blockMenus as $value) {
            $options             = (array) json_decode($value[ 'options' ], true);
            $options[ 'parent' ] = -1;

            $req->update('block', [
                    'options' => json_encode($options)
                ])
                ->where('block_id', '=', $value[ 'block_id' ])
                ->execute();
        }
    }
];
