<?php

namespace System\Services;

use Soosyze\Components\Util\Util;

class Modules
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Si le module est installé.
     *
     * @param string $name Nom du module.
     *
     * @return array
     */
    public function isInstall($name)
    {
        return $this->query
                ->from('module')
                ->where('name', $name)
                ->fetch();
    }

    /**
     * Si le module est requis par le module virtuel "core".
     *
     * @param string $key Nom du module.
     *
     * @return array
     */
    public function isRequiredCore($key)
    {
        return $this->query
                ->from('module_required')
                ->where('name_module', $key)
                ->where('name_required', 'Core')
                ->lists('name_required');
    }

    /**
     * Si le module est requis par un autre module installé.
     *
     * @param string $key Nom du module.
     *
     * @return array
     */
    public function isRequiredForModule($key)
    {
        return $this->query
                ->from('module')
                ->leftJoin('module_required', 'name', 'module_required.name_required')
                ->where('name', $key)
                ->isNotNull('name_module')
                ->lists('name_module');
    }

    public function listModuleActive(array $columns = [])
    {
        $moduleKey = [];
        $modules   = $this->query
            ->select($columns)
            ->from('module')
            ->fetchAll();
        foreach ($modules as $value) {
            $moduleKey[ $value[ 'name' ] ] = $value;
        }

        return $moduleKey;
    }

    public function listModuleActiveNotRequire(array $columns = [])
    {
        return $this->query
                ->select($columns)
                ->from('module')
                ->leftJoin('module_required', 'name', 'module_required.name_required')
                ->isNull('name_module')
                ->lists('name');
    }

    /**
     * Désinstalle un module.
     *
     * @param string $name Nom du module.
     */
    public function uninstallModule($name)
    {
        $this->query
            ->from('module')
            ->delete()
            ->where('name', $name)
            ->execute();

        $this->query
            ->from('module_required')
            ->delete()
            ->where('name_module', $name)
            ->execute();
    }

    /**
     * Installe un module.
     *
     * @param array $config Données du module.
     */
    public function create($config)
    {
        $required = isset($config[ 'required' ])
            ? $config[ 'required' ]
            : [];
        unset($config[ 'required' ]);

        foreach ($config[ 'controller' ] as $key => $controller) {
            $module                     = $config;
            $module[ 'controller' ]     = $controller;
            $module[ 'key_controller' ] = $key;

            $this->query
                ->insertInto('module', [ 'name', 'controller',
                    'version', 'description', 'package', 'locked', 'key_controller' ])
                ->values($module)
                ->execute();
        }

        foreach ($required as $require) {
            $this->query
                ->insertInto('module_required', [ 'name_module', 'name_required' ])
                ->values([ $config[ 'name' ], $require ])
                ->execute();
        }
    }

    public function getConfig($nameModule)
    {
        $config = $this->getConfigAll();

        return $config[ $nameModule ];
    }

    public function getModuleAll()
    {
        return array_merge($this->getModules(), $this->getModulesCore());
    }

    public function getModules($dir = MODULES_CONTRIBUED)
    {
        return Util::getFolder($dir);
    }

    public function getModulesCore()
    {
        return $this->getModules(MODULES_CORE);
    }

    public function getConfigModule($dir = MODULES_CONTRIBUED)
    {
        $config  = [];
        $modules = $this->getModules($dir);

        foreach ($modules as $module) {
            $file = $dir . DS . $module . DS . 'config.json';
            if (file_exists($file)) {
                $tmp    = Util::getJson($file);
                $config = array_merge($config, $tmp);
            }
        }

        return $config;
    }

    public function getConfigModuleCore()
    {
        return $this->getConfigModule(MODULES_CORE);
    }

    public function getConfigAll()
    {
        $conf = array_merge($this->getConfigModule(), $this->getConfigModuleCore());
        ksort($conf);

        return $conf;
    }
}
