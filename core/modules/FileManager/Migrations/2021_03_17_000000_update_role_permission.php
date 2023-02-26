<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $req->update('role_permission', [
                'permission_id' => 'filemanager.permission.admin'
            ])
            ->where('permission_id', '=', 'filemanager.profil.admin')
            ->execute();
    }
};
