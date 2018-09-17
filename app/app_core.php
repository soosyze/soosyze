<?php

use Soosyze\App;

require_once 'vendor/soosyze/framework/src/App.php';

class Core extends App
{
    public function loadServices()
    {
        return [
            "schema"   => [
                "class"     => "QueryBuilder\\Schema",
                "arguments" => [
                    "#database.host",
                    "#database.schema"
                ]
            ],
            "query"    => [
                "class"     => "QueryBuilder\\Query",
                "arguments" => [
                    "@schema"
                ]
            ],
            "template" => [
                "class"     => "Template\\TemplatingHtml",
                "arguments" => [
                    "@core",
                    "@config"
                ]
            ]
        ];
    }

    public function loadModules()
    {
        $modules = [
            "TodoController" => new TodoModule\Controller\TodoController()
        ];

        if (empty($this->get('config')->get('settings.time_installed'))) {
            $modules[ 'Install' ] = new Install\Controller\Install();

            return $modules;
        }

        $data = $this->get('query')->select('name', 'controller')->from('module')->fetchAll();
        foreach ($data as $value) {
            $modules[ $value[ 'name' ] ] = new $value[ 'controller' ]();
        }

        return $modules;
    }
}
