<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        $req
            ->update('node_type_field', [
                'field_rules' => '!required|image|max:800kb'
            ])
            ->where('field_rules', 'like', '%file-name-image%')
            ->execute();
    }
];
