<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('node', function (TableBuilder $table) {
            $table
                    ->integer('node_status_id')->valueDefault(3);
        });

        $nodes = $req->from('node')->fetchAll();
        foreach ($nodes as $node) {
            if ($node[ 'published' ]) {
                $req->update('node', [
                        'node_status_id' => 1
                    ])
                    ->where('id', '=', $node[ 'id' ])
                    ->execute();
            }
        }
    }
};
