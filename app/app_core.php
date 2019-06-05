<?php

use Soosyze\App;

require_once ROOT . '/vendor/soosyze/framework/src/App.php';

class Core extends App
{
    public function loadServices()
    {
        return [
            'schema'   => [
                'class'     => 'SoosyzeCore\\QueryBuilder\\Services\\Schema',
                'arguments' => [
                    '#database.host',
                    '#database.schema'
                ]
            ],
            'query'    => [
                'class'     => 'SoosyzeCore\\QueryBuilder\\Services\\Query',
                'arguments' => [
                    '@schema'
                ]
            ],
            'template' => [
                'class'     => 'SoosyzeCore\\Template\\Services\\TemplatingHtml',
                'arguments' => [
                    '@core',
                    '@config'
                ]
            ],
            'file'     => [
                'class'     => 'SoosyzeCore\\FileSystem\\Services\\File',
                'arguments' => [
                    '@core'
                ]
            ]
        ];
    }

    public function loadModules()
    {
        if (!$this->get('config')->get('settings.time_installed')) {
            $modules[ 'Install' ] = new SoosyzeCore\System\Controller\Install();

            return $modules;
        }

        $data = $this->get('query')->select('key_controller', 'controller')->from('module_controller')->fetchAll();
        foreach ($data as $value) {
            $modules[ $value[ 'key_controller' ] ] = new $value[ 'controller' ]();
        }

        return $modules;
    }
}
