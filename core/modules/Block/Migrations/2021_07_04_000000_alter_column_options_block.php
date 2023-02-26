<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $blocks = $req->from('block')->fetchAll();

        $sch->alterTable('block', function (TableAlter $table) {
            $table->text('options')->nullable()->modify();
        });

        foreach ($blocks as $block) {
            $req->update('block', [
                    'options' => $block[ 'options' ]
                ])
                ->where('block_id', '=', $block[ 'block_id' ])
                ->execute();
        }
    }
};
