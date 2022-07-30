<?php

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        $req->update('profil_file', [
                'folder_show' => '/download'
            ])
            ->where('folder_show', '=', '/dowload')
            ->execute();
    }
];
