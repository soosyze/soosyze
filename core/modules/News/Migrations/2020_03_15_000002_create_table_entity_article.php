<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->createTableIfNotExists('entity_article', function (TableBuilder $tb) {
            $tb->increments('article_id');
            $tb->string('image');
            $tb->text('summary');
            $tb->text('body');
            $tb->integer('reading_time')->comment('In minute');
        });

        /** @phpstan-var array{ field_id: int } $imageField */
        $imageField   = $req->from('field')->where('field_name', '=', 'image')->fetch();
        /** @phpstan-var array{ field_id: int } $summaryField */
        $summaryField = $req->from('field')->where('field_name', '=', 'summary')->fetch();
        /** @phpstan-var array{ field_id: int } $bodyField */
        $bodyField    = $req->from('field')->where('field_name', '=', 'body')->fetch();
        /** @phpstan-var array{ field_id: int } $readingField */
        $readingField = $req->from('field')->where('field_name', '=', 'reading_time')->fetch();

        $req->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_weight', 'field_label', 'field_rules',
                'field_description', 'field_show_form'
            ])
            ->values([
                'article', $imageField[ 'field_id' ], 1, 'Picture',
                'required_without:file-name-image|!required|image|max:800kb',
                'The weight of the image must be less than or equal to 800ko',
                true
            ])
            ->values([
                'article', $summaryField[ 'field_id' ], 2, 'Summary',
                'required|string|max:512',
                'Briefly summarize your article in less than 512 characters',
                true
            ])
            ->values([
                'article', $bodyField[ 'field_id' ], 3, 'Body',
                'string',
                '',
                true
            ])
            ->values([
                'article', $readingField[ 'field_id' ], 4, 'Reading time',
                'number|min:1',
                '',
                false
            ])
            ->execute();

        /** @phpstan-var array<array{ id: int, field: string }> $nodes */
        $nodes = $req->from('node')->where('type', '=', 'article')->fetchAll();
        foreach ($nodes as $i => $node) {
            /** @phpstan-var array $field */
            $field = unserialize($node[ 'field' ]);
            $req->insertInto('entity_article', [
                    'article_id', 'image', 'summary', 'body', 'reading_time'
                ])
                ->values([
                    $i,
                    $field[ 'image' ] ?? '',
                    $field[ 'summary' ] ?? '',
                    $field[ 'body' ] ?? '',
                    1
                ])
                ->execute();

            $req->update('node', [ 'entity_id' => $i ])
                ->where('id', '=', $node[ 'id' ])
                ->execute();
        }
    }
};
