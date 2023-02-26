<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('node_type', function (TableBuilder $table) {
            $table->string('node_type_color', 7)->valueDefault('#e6e7f4');
        });

        $colors = [
            'article'       => '#ddd',
            'documentation' => '#a8beff',
            'page'          => '#7fff88',
            'page_faq'      => '#7ff6ff',
            'page_gallery'  => '#f6ffa8',
            'page_module'   => '#b299ff',
            'page_private'  => '#005706',
            'page_theme'    => '#b299ff'
        ];

        $nodeTypes = $req->from('node_type')->fetchAll();

        foreach ($nodeTypes as $type) {
            if (isset($colors[ $type[ 'node_type' ] ])) {
                $req->update('node_type', [
                        'node_type_color' => $colors[ $type[ 'node_type' ] ]
                    ])
                    ->where('node_type', '=', $type[ 'node_type' ])
                    ->execute();
            }
        }
    }
};
