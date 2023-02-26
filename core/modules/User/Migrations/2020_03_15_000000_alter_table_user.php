<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableAlter;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('user', function (TableAlter $ta) {
            $ta->dropColumn('salt');
            $ta->text('token_connected')->nullable();
        });
        $req->update('user', [
                'password' => password_hash('Soosyze2020&', PASSWORD_DEFAULT)
            ])
            ->where('user_id', '=', 1)
            ->execute();
    }
};
