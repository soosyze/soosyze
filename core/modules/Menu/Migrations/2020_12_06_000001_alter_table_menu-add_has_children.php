<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('menu_link', function (TableAlter $table) {
            $table->boolean('has_children')->valueDefault(false);
        });

        $links = $req->from('menu_link')->fetchAll();

        foreach ($links as $link) {
            if ($link[ 'parent' ] !== -1) {
                $req->update('menu_link', [
                        'has_children' => true
                    ])
                    ->where('id', '=', $link[ 'parent' ])
                    ->execute();
            }
        }
    }
};
