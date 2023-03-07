<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $nodes = $req->from('node')->fetchAll();

        $sch->alterTable('node', function (TableAlter $table) {
            $table->dropColumn('date_changed');
            $table->dropColumn('date_created');
        });
        $sch->alterTable('node', function (TableAlter $table) {
            $table->string('date_changed');
            $table->string('date_created');
        });

        foreach ($nodes as $node) {
            $req->update('node', [
                'date_changed' => (string) $node['date_changed'],
                'date_created' => (string) $node['date_created']
            ])
                ->where('id', '=', $node['id'])
                ->execute();
        }
    }
};
