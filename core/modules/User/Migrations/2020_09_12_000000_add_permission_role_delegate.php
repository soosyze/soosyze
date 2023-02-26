<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $req->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'role.all' ])
            ->execute();
    }
};
