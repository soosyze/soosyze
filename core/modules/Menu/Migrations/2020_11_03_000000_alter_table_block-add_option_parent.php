<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        if (!$sch->hasTable('block')) {
            return;
        }

        /**
         * @phpstan-var array<
         *      array{
         *          block_id: int,
         *          options: string
         *      }
         * > $blockMenus
         */
        $blockMenus = $req->from('block')->where('hook', '=', 'menu')->fetchAll();

        foreach ($blockMenus as $value) {
            $options             = (array) json_decode($value[ 'options' ], true);
            $options[ 'parent' ] = -1;

            $req->update('block', [
                    'options' => json_encode($options)
                ])
                ->where('block_id', '=', $value[ 'block_id' ])
                ->execute();
        }
    }
};
