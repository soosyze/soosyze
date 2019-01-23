<?php

use Soosyze\App;

require_once 'vendor/soosyze/framework/src/App.php';

class Core extends App
{
    public function loadServices()
    {
        return [
            'schema'   => [
                'class'     => 'QueryBuilder\\Schema',
                'arguments' => [
                    '#database.host',
                    '#database.schema'
                ]
            ],
            'query'    => [
                'class'     => 'QueryBuilder\\Query',
                'arguments' => [
                    '@schema'
                ]
            ],
            'template' => [
                'class'     => 'Template\\Services\\TemplatingHtml',
                'arguments' => [
                    '@core',
                    '@config'
                ]
            ]
        ];
    }

    public function loadModules()
    {
        if (!$this->get('config')->get('settings.time_installed')) {
            $modules[ 'Install' ] = new Install\Controller\Install();

            return $modules;
        }

        $data = $this->get('query')->select('key_controller', 'controller')->from('module')->fetchAll();
        foreach ($data as $value) {
            $modules[ $value[ 'key_controller' ] ] = new $value[ 'controller' ]();
        }

        return $modules;
    }
}
