<?php

namespace SoosyzeCore\System\Services;

use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\System\Migration;

class Composer
{
    /**
     * @var \Core
     */
    protected $core;

    /**
     * @var Modules
     */
    protected $module;

    /**
     * Les informations des fichiers composer des modules.
     *
     * @var array
     */
    protected $composer = [];

    public function __construct($module, $core)
    {
        $this->module = $module;
        $this->core   = $core;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function validComposer($title, array $data)
    {
        $errors    = [];
        $validator = (new Validator())
            ->setRules([
                'name'        => 'required|string',
                'type'        => 'required|string|equal:soosyze-module',
                'description' => 'required|string|max:255',
                'version'     => 'required',
                'autoload'    => 'required|array'
            ])
            ->setInputs($data);

        if (!$validator->isValid()) {
            $errors += $validator->getErrors();
        } elseif (empty($data[ 'autoload' ][ 'psr-4' ]) || !is_array($data[ 'autoload' ][ 'psr-4' ])) {
            $errors[] = t('The namespace information for the :name module does not exist.', [':name' => $title]);
        } else {
            $migration = array_keys($data[ 'autoload' ][ 'psr-4' ])[ 0 ] . 'Installer';
            if (!class_exists($migration)) {
                $errors[] = t('The installation scripts for the :name module do not exist.', [':name' => $title ]);
            } elseif (!(new $migration() instanceof Migration)) {
                $errors[] = t('The :name install class does not implement the migration interface.', [':name' => $migration]);
            }
        }

        return $errors + $this->validComposerModule($title, $data);
    }

    public function validRequireModule($title, array $composer)
    {
        if (!isset($composer[ $title ][ 'extra' ][ 'soosyze' ][ 'require' ])) {
            return [];
        }
        $errors = [];

        foreach ($composer[ $title ][ 'extra' ][ 'soosyze' ][ 'require' ] as $module => $version) {
            /* Si les sources du module requis n'existe pas. */
            if (!isset($composer[ $module ])) {
                $errors[] = t('The :name module source files (v:version) do not exist.', [
                    ':name'    => $module,
                    ':version' => $version
                ]);

                continue;
            }
            /* Si le module requis n'est pas installé. */
            if (!($require = $this->module->has($module))) {
                $errors[] = t('The :name1 (v:version) module required by :name2 is not installed.', [
                    ':name1'   => $module,
                    ':version' => $version,
                    ':name2'   => $title
                ]);
            }
            /* Si le module requis n'est pas dans la version attendue. */
            elseif (!$this->validVersion($version, $require[ 'version' ])) {
                $errors[] = t('The :name1 module require the :name2 (v:version) module, currently (v:version_current).', [
                    ':name1'           => $title,
                    ':name2'           => $module,
                    ':version'         => $version,
                    ':version_current' => $require[ 'version' ]
                ]);
            }
        }

        return $errors;
    }

    public function getComposerModule($name)
    {
        $composer = $this->getAllComposer();

        return $composer[ $name ];
    }

    public function getAllComposer($reload = false)
    {
        if (!empty($this->composer) || $reload) {
            return $this->composer;
        }
        $module            = $this->core->getDir('modules', 'core/modules');
        $module_contribued = $this->core->getDir('modules_contributed', 'app/modules');

        $this->composer = $this->getComposerModules($module_contribued) + $this->getComposerModules($module) + $this->getComposerInstalledModules();

        return $this->composer;
    }

    public function getTitle($key)
    {
        return $this->getComposerModule($key)[ 'extra' ][ 'soosyze' ][ 'title' ];
    }

    public function getNamespace($key)
    {
        return array_keys($this->getComposerModule($key)[ 'autoload' ][ 'psr-4' ])[ 0 ];
    }

    public function validVersion($version, $comparator, $inverse = false)
    {
        if (!preg_match('/^(>=|<=|<|>|!=)?(\d+(?:\.\d+|-\w*){0,3})(\.\*)?$/', $version, $matches)) {
            return [ 'error' ];
        }
        $operator = !empty($matches[ 1 ])
            ? $matches[ 1 ]
            : '==';

        return $inverse
            ? version_compare($comparator, $matches[ 2 ], $operator)
            : version_compare($matches[ 2 ], $comparator, $operator);
    }

    protected function getComposerModules($dir)
    {
        $config  = [];
        $modules = Util::getFolder($dir);

        foreach ($modules as $module) {
            $file = $dir . DS . $module . DS . 'composer.json';
            if (!file_exists($file)) {
                continue;
            }
            $composer = Util::getJson($file);
            if (!isset($composer[ 'type' ]) || $composer[ 'type' ] !== 'soosyze-module' || !isset($composer[ 'extra' ][ 'soosyze' ][ 'title' ])) {
                continue;
            }

            $config[ $composer[ 'extra' ][ 'soosyze' ][ 'title' ] ] = $composer;
        }

        return $config;
    }

    protected function getComposerInstalledModules()
    {
        $config  = [];
        $modules = file_exists('vendor/composer/installed.json')
            ? Util::getJson('vendor/composer/installed.json')
            : [];
        foreach ($modules as $composer) {
            if (!isset($composer[ 'type' ]) || $composer[ 'type' ] !== 'soosyze-module' || !isset($composer[ 'extra' ][ 'soosyze' ][ 'title' ])) {
                continue;
            }

            $config[ $composer[ 'extra' ][ 'soosyze' ][ 'title' ] ] = $composer;
        }

        return $config;
    }

    /**
     * Vérifie la conformité des données du module de son fichier composer.
     *
     * @param array $composer Données du fichier composer.json.
     *
     * @return array La liste des erreurs.
     */
    protected function validComposerModule($title, array $composer)
    {
        $errors = [];
        if (!isset($composer[ 'extra' ][ 'soosyze' ]) && !is_array($composer[ 'extra' ][ 'soosyze' ])) {
            $errors[] = t('The :name module information does not exist.', [':name' => $title]);

            return $errors;
        }

        $validator = (new Validator())
                ->setRules([
                    'title'      => 'required|string|max:128',
                    'package'    => 'required|string|max:128',
                    'controller' => 'required|string',
                    'icon'       => '!required|array',
                    'require'    => '!required|array'
                ])->setInputs($composer[ 'extra' ][ 'soosyze' ]);

        if (!$validator->isValid()) {
            $errors += $validator->getErrors();
        } elseif (empty($composer[ 'extra' ][ 'soosyze' ][ 'controller' ])) {
            $errors[] = t('The information on the controllers of the :name module does not exist.', [':name' => $title]);
        } else {
            $controller = $composer[ 'extra' ][ 'soosyze' ][ 'controller' ];
            if (!class_exists($controller)) {
                $errors[] = t('The :controller controller of the :name module is not found by the autoloader.', [
                    ':controller' => $controller,
                    ':name'       => $title
                ]);
            }
        }

        return $errors;
    }
}
