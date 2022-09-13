<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        $req->update('block', [ 'key_block' => 'text' ])
            ->in('key_block', [
                'button',
                'card_ui',
                'three'
            ])
            ->execute();
    }
];
