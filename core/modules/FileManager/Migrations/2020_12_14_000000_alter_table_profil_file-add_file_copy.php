<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('profil_file', function (TableAlter $table) {
            $table->boolean('file_copy')->valueDefault(true);
        });

        $profils = $req->from('profil_file_role')->where('role_id', '=', 1)->fetchAll();

        foreach ($profils as $profil) {
            $req->update('profil_file', [
                    'file_copy' => false
                ])
                ->where('profil_file_id', '=', $profil[ 'profil_file_id' ])
                ->execute();
        }
    }
};
