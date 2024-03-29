<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('field', function (TableAlter $ta) {
            $ta->dropColumn('field_rules');
        });
        $sch->truncateTable('field');
        $req->insertInto('field', [
                'field_name', 'field_type'
            ])
            ->values([ 'body', 'textarea' ])
            ->values([ 'image', 'image' ])
            ->values([ 'summary', 'textarea' ])
            ->values([ 'reading_time', 'number' ])
            ->values([ 'weight', 'number' ])
            ->execute();

        $sch->alterTable('node_type_field', function (TableAlter $ta) {
            $ta->string('field_rules')->valueDefault('');
            $ta->boolean('field_show')->valueDefault(true);
            $ta->boolean('field_show_form')->valueDefault(true);
            $ta->boolean('field_show_label')->valueDefault(false);
            $ta->text('field_option')->valueDefault('');
            $ta->integer('field_weight_form')->valueDefault(1);
        });
        $sch->truncateTable('node_type_field');
        $req->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_label', 'field_weight', 'field_rules',
                'field_option'
            ])
            ->values([ 'page', 1, 'Body', 2, '!required|string', '' ])
            ->execute();
    }
};
