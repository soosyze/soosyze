<?php

namespace SoosyzeCore\System\Services;

class Modules
{
    /**
     * @var \SoosyzeCore\QueryBuilder\Services\Query
     */
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Si le module est installé.
     *
     * @param string $title Nom du module.
     *
     * @return array
     */
    public function has($title)
    {
        return $this->query
                ->from('module_active')
                ->where('title', $title)
                ->fetch();
    }

    /**
     * Si le module est requis par le module virtuel "core".
     *
     * @param string $title Nom du module.
     *
     * @return array
     */
    public function isRequiredCore($title)
    {
        return $this->query
                ->from('module_require')
                ->where('title_module', $title)
                ->where('title_required', 'Core')
                ->lists('title_required');
    }

    /**
     * Si le module est requis par un autre module installé.
     *
     * @param string $title Nom du module.
     *
     * @return array
     */
    public function isRequiredForModule($title)
    {
        $output = $this->query
            ->from('module_active')
            ->leftJoin('module_require', 'title', 'module_require.title_required')
            ->where('title', $title)
            ->isNotNull('title_module')
            ->lists('title_module');

        return array_unique($output);
    }

    public function listModuleActive(array $columns = [])
    {
        $modules = $this->query
            ->select($columns)
            ->from('module_active')
            ->fetchAll();

        foreach ($modules as $value) {
            $modules[ $value[ 'title' ] ] = $value;
        }

        return $modules;
    }

    public function listModuleActiveNotRequire(array $columns = [])
    {
        return $this->query
                ->select($columns)
                ->from('module_active')
                ->leftJoin('module_require', 'title', 'module_require.title_required')
                ->isNull('title_module')
                ->lists('title');
    }

    /**
     * Désinstalle un module.
     *
     * @param string $title Nom du module.
     */
    public function uninstallModule($title)
    {
        $this->query
            ->from('module_active')
            ->delete()
            ->where('title', $title)
            ->execute();

        $this->query
            ->from('module_require')
            ->delete()
            ->where('title_module', $title)
            ->execute();

        $this->query
            ->from('module_controller')
            ->delete()
            ->where('title', $title)
            ->execute();
    }

    /**
     * Créer un module à partir du contenu de son fichier composer.json
     *
     * @param array $composer Données du module.
     */
    public function create(array $composer)
    {
        $module = $composer[ 'extra' ][ 'soosyze' ];
        /* Enregistrement du module. */
        $this->query
            ->insertInto('module_active', [ 'title', 'version' ])
            ->values([ $module[ 'title' ], $composer[ 'version' ] ])
            ->execute();

        /* Enregistrement des contrôleurs. */
        $this->query
            ->insertInto('module_controller', [ 'title', 'controller' ])
            ->values([ $module[ 'title' ], $module[ 'controller' ] ])
            ->execute();

        if (isset($module[ 'require' ])) {
            /* Enregistrement des dépendances. */
            $this->query->insertInto('module_require', [
                'title_module', 'title_required', 'version'
            ]);

            foreach ($module[ 'require' ] as $require => $version) {
                $this->query->values([ $module[ 'title' ], $require, $version ]);
            }

            $this->query->execute();
        }
    }
}
