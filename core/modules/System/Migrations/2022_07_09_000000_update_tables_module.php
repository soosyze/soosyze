<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $moduleControllers = $req
            ->from('module_controller')
            ->fetchAll();

        /** @var array{controller: string, title: string} $module */
        foreach ($moduleControllers as $module) {
            $req
                ->update('module_controller', [
                    'controller' => str_replace(
                        [ 'SoosyzeCore', 'SoosyzeExtension' ],
                        [ 'Soosyze\Core\Modules', 'Soosyze\App\Modules' ],
                        $module[ 'controller' ]
                    )
                ])
                ->where('title', '=', $module[ 'title' ])
                ->execute();
        }

        $moduleRequires = $req
            ->from('module_require')
            ->fetchAll();

        foreach ($moduleRequires as $module) {
            $req
                ->update('module_require', [
                    'version' => '2.0.*'
                ])
                ->where('title_module', '=', $module[ 'title_module' ])
                ->execute();
        }

        $moduleActives = $req
            ->from('module_active')
            ->fetchAll();

        foreach ($moduleActives as $module) {
            $req
                ->update('module_active', [
                    'version' => '2.0.0'
                ])
                ->where('title', '=', $module[ 'title' ])
                ->execute();
        }
    }
};
