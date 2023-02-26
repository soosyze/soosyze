<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->createTableIfNotExists('entity_page_private', function (TableBuilder $tb) {
            $tb->increments('page_private_id');
            $tb->text('body');
        });
        $req->insertInto('node_type', [
                'node_type',
                'node_type_name',
                'node_type_description',
                'node_type_icon'
            ])
            ->values([
                'page_private',
                'Private page',
                'Use the private pages for content reserved for your members.',
                'far fa-file'
            ])
            ->execute();

        $req->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_label', 'field_weight', 'field_rules'
            ])
            ->values([ 'page_private', 1, 'Body', 2, '!required|string' ])
            ->execute();
    }
};
