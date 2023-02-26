<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('block', function (TableAlter $table) {
            $table->text('theme')->valueDefault('public');
        });

        $req->update('block', [ 'theme' => 'admin' ])
            ->where('block_id', '=', 1)
            ->execute();

        $blocks = $req->from('block')->fetchAll();
        /** @phpstan-var array{ pages: string, block_id: int } $block */
        foreach ($blocks as $block) {
            $req->update('block', [
                    'pages' => str_replace('admin/%' . PHP_EOL, '', $block[ 'pages' ])
                ])
                ->where('block_id', '=', $block[ 'block_id' ])
                ->execute();
        }
    }
};
