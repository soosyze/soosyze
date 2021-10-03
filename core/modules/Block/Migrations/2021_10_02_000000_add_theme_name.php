<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('block', function (TableAlter $table) {
            $table->text('theme')->valueDefault('public');
        });

        $req->update('block', [ 'theme' => 'admin' ])
            ->where('block_id', '=', 1)
            ->execute();

        $blocks = $req->from('block')->fetchAll();
        foreach ($blocks as $block) {
            $req->update('block', [
                    'pages' => str_replace('admin/%' . PHP_EOL, '', $block[ 'pages' ])
                ])
                ->where('block_id', '=', $block[ 'block_id' ])
                ->execute();
        }
    }
];
