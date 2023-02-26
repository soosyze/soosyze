<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $req->update('node_type', [
                'node_type_icon' => 'fas fa-newspaper'
            ])
            ->where('node_type', '=', 'article')
            ->execute();
    }
};
