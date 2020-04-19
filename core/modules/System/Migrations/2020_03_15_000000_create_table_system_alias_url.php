<?php

use Queryflatfile\Schema;
use Queryflatfile\TableBuilder;

return [
    'up' => function (Schema $sch) {
        $sch->createTableIfNotExists('system_alias_url', function (TableBuilder $table) {
            $table->string('source')
                    ->string('alias');
        });
    }
];
