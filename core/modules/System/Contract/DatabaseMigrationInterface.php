<?php

namespace Soosyze\Core\Modules\System\Contract;

use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

interface DatabaseMigrationInterface
{
    public function up(Schema $sch, Request $req): void;
}
