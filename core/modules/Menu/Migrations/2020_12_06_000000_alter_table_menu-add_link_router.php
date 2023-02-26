<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;
use Soosyze\Queryflatfile\TableBuilder;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $sch->alterTable('menu_link', function (TableBuilder $table) {
            $table->string('link_router')->nullable();
        });

        $links = $req->from('menu_link')->isNotNull('key')->fetchAll();

        foreach ($links as $link) {
            $alias = $req->from('system_alias_url')
                ->where('alias', '=', $link[ 'link' ])
                ->fetch();

            $linkRouter = empty($alias)
                ? $link[ 'link' ]
                : $alias[ 'source' ];

            $req->update('menu_link', [
                    'link_router' => $linkRouter
                ])
                ->where('link', '=', $link[ 'link' ])
                ->execute();
        }
    }
};
