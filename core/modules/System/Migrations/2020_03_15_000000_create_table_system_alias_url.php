<?php

use Queryflatfile\Schema;
use Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch) {
        $sch->createTableIfNotExists('system_alias_url', function (TableBuilder $tb) {
            $tb->string('source');
            $tb->string('alias');
        });
    }
];
