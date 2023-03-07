<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('block', function (TableAlter $table) {
            $table->string('key_block')->nullable();
        });

        $blocks = $req->from('block')->where('hook', 'like', 'news.%')->fetchAll();
        foreach ($blocks as $block) {
            $req->update('block', [ 'key_block' => $block[ 'hook' ] ])
                ->where('block_id', '=', $block[ 'block_id' ])
                ->execute();
        }
    }
};
