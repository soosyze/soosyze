<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('block', function (TableBuilder $table) {
            $table->string('key_block')->nullable();
        });

        $blocks = $req->from('block')->where('hook', 'like', 'news.%')->fetchAll();
        foreach ($blocks as $block) {
            $req->update('block', [ 'key_block' => $block[ 'hook' ] ])
                ->where('block_id', '=', $block[ 'block_id' ])
                ->execute();
        }
    }
];
