<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('menu_link', function (TableAlter $ta): void {
            $ta->renameColumn('id', 'link_id');
            $ta->integer('menu_id');
        });
        $sch->alterTable('menu', function (TableAlter $ta): void {
            $ta->increments('menu_id');
        });

        $menusOld = $req->from('menu')->lists('menu_id', 'name');
        $menuLinks = $req->from('menu_link')->fetchAll();
        foreach ($menuLinks as $link) {
            $req->update('menu_link', [ 'menu_id' => $menusOld[ $link[ 'menu' ] ] ])
                ->where('link_id', '=', $link[ 'link_id' ])
                ->execute();
        }

        $sch->alterTable('menu_link', function (TableAlter $ta): void {
            $ta->dropColumn('menu');
        });
        $sch->alterTable('menu', function (TableAlter $ta): void {
            $ta->dropColumn('name');
        });

        $menuBlocks = $req->from('block')->where('key_block', '=', 'menu')->fetchAll();
        /** @phpstan-var array{ block_id: int, options: string } $block */
        foreach ($menuBlocks as $block) {
            $options = (array) json_decode($block[ 'options' ], true);

            $options[ 'menu_id' ] = $menusOld[ $options[ 'name' ] ];
            unset($options[ 'name' ]);

            $req->update('block', [ 'options' => json_encode($options) ])
                ->where('block_id', '=', $block[ 'block_id' ])
                ->execute();
        }
    }
};
