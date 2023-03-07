<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('menu_link', function (TableAlter $table) {
            $table->boolean('target_link')->valueDefault(false)->modify();
        });
    }
};
