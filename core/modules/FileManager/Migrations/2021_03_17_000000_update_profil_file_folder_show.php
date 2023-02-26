<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $req->update('profil_file', [
                'folder_show' => '/download'
            ])
            ->where('folder_show', '=', '/dowload')
            ->execute();
    }
};
