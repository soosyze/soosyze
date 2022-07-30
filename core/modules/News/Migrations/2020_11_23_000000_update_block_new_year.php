<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        $req
            ->update('block', [
                'hook'      => 'news.archive',
                'key_block' => 'news.archive',
                'options'   => json_encode([ 'expand' => true ])
            ])
            ->where('key_block', '=', 'news.month')
            ->execute();

        $req
            ->update('block', [
                'hook'      => 'news.archive',
                'key_block' => 'news.archive',
                'options'   => json_encode([ 'expand' => false ])
            ])
            ->where('key_block', '=', 'news.year')
            ->execute();
    }
];
