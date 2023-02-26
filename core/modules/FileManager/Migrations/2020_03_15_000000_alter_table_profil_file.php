<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        /**
         * @phpstan-var array<
         *      array{
         *          profil_file_id: int,
         *          folder_show: string
         *      }
         *  > $profils
         */
        $profils = $req->from('profil_file')->fetchAll();
        foreach ($profils as $profil) {
            if (strpos($profil[ 'folder_show' ], '%uid') === false) {
                continue;
            }
            $req->update('profil_file', [
                    'folder_show' => str_replace('%uid', ':user_id', $profil[ 'folder_show' ])
                ])
                ->where('profil_file_id', '=', $profil[ 'profil_file_id' ])
                ->execute();
        }
    }
};
