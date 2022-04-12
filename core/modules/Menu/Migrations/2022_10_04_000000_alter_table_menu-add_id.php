<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
        $sch->alterTable('menu_link', function (TableAlter $ta): void {
            $ta->renameColumn('id', 'link_id');
            $ta->integer('menu_id');
        });
        $sch->alterTable('menu', function (TableAlter $ta): void {
            $ta->dropColumn('name');
            $ta->increments('menu_id');
        });

        $menuLinks = $req->from('menu_link')->fetchAll();
        foreach ($menuLinks as $link) {
            $menuId = 1;
            if ($link[ 'menu' ] === 'menu-main') {
                $menuId = 2;
            } elseif ($link[ 'menu' ] === 'menu-user') {
                $menuId = 3;
            }

            $req->update('menu_link', [ 'menu_id' => $menuId ])
                ->where('link_id', '=', $link[ 'link_id' ])
                ->execute();
        }

        $sch->alterTable('menu_link', function (TableAlter $ta): void {
            $ta->dropColumn('menu');
        });

        $menuBlocks = $req->from('block')->where('key_block', '=', 'menu')->fetchAll();
        /** @phpstan-var array{ block_id: int, options: string } $block */
        foreach ($menuBlocks as $block) {
            $options = (array) json_decode($block[ 'options' ], true);

            $options[ 'menu_id' ] = 1;
            if ($options[ 'name' ] === 'menu-main') {
                $options[ 'menu_id' ] = 2;
            } elseif ($options[ 'name' ] === 'menu-user') {
                $options[ 'menu_id' ] = 3;
            }
            unset($options[ 'name' ]);

            $req->update('block', [ 'options' => json_encode($options) ])
                ->where('block_id', '=', $block[ 'block_id' ])
                ->execute();
        }
    }
];
