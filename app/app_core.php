<?php

use Soosyze\App;
use Soosyze\Components\Util\Util;

require_once 'vendor/soosyze/framework/src/App.php';

class Core extends App
{
    public function loadModules()
    {
        $modules = [
            "QueryBuilder"   => new QueryBuilder\Controller\QueryBuilder()
        ];

        if (empty($this->getConfig('settings.time_installed'))) {
            $modules[ 'Install' ] = new Install\Controller\Install();

            return $modules;
        }

        $load = $this->loadModulesCMS();

        return array_merge($modules, $load);
    }

    public function loadModulesCMS()
    {
        $output     = [];
        $modulesCMS = ROOT . 'app/data/module.json';
        if (!file_exists($modulesCMS)) {
            return [];
        }
        $dataModules = Util::getJson($modulesCMS);
        foreach ($dataModules as $module) {
            $output[ $module[ 'name' ] ] = new $module[ 'controller' ]();
        }

        return $output;
    }
}
