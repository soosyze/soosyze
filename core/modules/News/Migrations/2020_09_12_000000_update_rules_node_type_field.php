<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $req
            ->update('node_type_field', [
                'field_rules' => '!required|image|max:800kb'
            ])
            ->where('field_rules', 'like', '%file-name-image%')
            ->execute();
    }
};
