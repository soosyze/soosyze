<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $nodeTypes = $req->from('node_type')->fetchAll();

        $req->from('role_permission')
            ->delete()
            ->where('permission_id', 'like', 'node%')
            ->execute();

        $req->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'node.administer' ])
            ->values([ 2, 'node.show.published.page_private' ]);

        foreach ($nodeTypes as $nodeType) {
            $req->values([ 2, "node.show.published.{$nodeType[ 'node_type' ]}" ])
                ->values([ 1, "node.show.published.{$nodeType[ 'node_type' ]}" ]);
        }

        $req->execute();
    }
};
