<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->createTableIfNotExists('node_status', function (TableBuilder $tb) {
            $tb->increments('node_status_id');
            $tb->text('node_status_name');
        });

        $req->insertInto('node_status', [
                'node_status_id', 'node_status_name'
            ])
            ->values([ 1, 'Published' ])
            ->values([ 2, 'Pending publication' ])
            ->values([ 3, 'Draft' ])
            ->values([ 4, 'Archived' ])
            ->execute();
    }
};
