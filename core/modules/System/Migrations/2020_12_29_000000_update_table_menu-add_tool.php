<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        if (!$sch->hasTable('menu')) {
            return;
        }
        $req
            ->insertInto('menu_link', [
                'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                'system.tool.admin', 'fa fa-tools', 'Tool', 'admin/tool',
                'menu-admin', 7, -1
            ])
            ->execute();

        $req->insertInto('role_permission', [ 'role_id', 'permission_id' ])
            ->values([ 3, 'system.tool.manage' ])
            ->values([ 3, 'system.tool.action' ])
            ->execute();
    }
};
