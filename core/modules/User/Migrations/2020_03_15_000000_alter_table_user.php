<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Queryflatfile\TableAlter;

return [
    'up' => function (Schema $sch, Request $req) {
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
];
