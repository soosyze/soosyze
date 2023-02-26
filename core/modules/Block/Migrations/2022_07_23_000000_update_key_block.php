<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $req->update('block', [ 'key_block' => 'text' ])
            ->in('key_block', [
                'button',
                'card_ui',
                'three'
            ])
            ->execute();
    }
};
