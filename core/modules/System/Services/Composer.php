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
            $errors[] = 'Les informations sur ne namespace du module <b>' . htmlspecialchars($title) . '</b> n\'existe pas.';
        } else {
            $migration = array_keys($data[ 'autoload' ][ 'psr-4' ])[ 0 ] . 'Installer';
            if (!class_exists($migration)) {
                $errors[] = 'Les scripts d\'installation du module <b>' . htmlspecialchars($title) . '</b> n\'existe pas.';
            } elseif (!(new $migration() instanceof Migration)) {
                $errors[] = 'La classe d\'installation <b>' . htmlspecialchars($migration) . '</b> n\'implemente pas l\'interface Migration.';
            }
        }

        return $errors + $this->validComposerModule($title, $data);
    }

    public function validRequireModule($title, array $composer)
    {
        if (!isset($composer[ $title ][ 'extra' ][ 'soosyze-module' ][ 'require' ])) {
            return [];
        }
        $errors = [];

        foreach ($composer[ $title ][ 'extra' ][ 'soosyze-module' ][ 'require' ] as $module => $version) {
            /* Si les sources du module requis n'existe pas. */
            if (!isset($composer[ $module ])) {
                $errors[] = 'Les fichiers sources de module'
                    . ' <b>' . htmlspecialchars($module) . '</b>'
                    . ' (v<b>' . htmlspecialchars($version) . '</b>) n\'existe pas.';

                continue;
            }
            /* Si le module requis n'est pas installé. */
            if (!($require = $this->module->has($module))) {
                $errors[] = 'Le module <b>' . htmlspecialchars($module) . '</b>'
                    . ' (v<b>' . htmlspecialchars($version) . '</b>)'
                    . ' requis par <b>' . htmlspecialchars($title) . '</b> n\'est pas installé.';
            }
            /* Si le module requis n'est pas dans la version attendue. */
            elseif (!$this->validVersion($version, $require[ 'version' ])) {
                $errors[] = 'Le module <b>' . htmlspecialchars($title) . '</b>'
                    . ' require le module <b>' . htmlspecialchars($module) . '</b>'
                    . ' (v<b>' . htmlspecialchars($version) . '</b>, actuellement'
                    . ' v<b>' . htmlspecialchars($require[ 'version' ]) . '</b>).';
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
        return $this->getComposerModule($key)[ 'extra' ][ 'soosyze-module' ][ 'title' ];
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
            if (!isset($composer[ 'type' ]) || $composer[ 'type' ] !== 'soosyze-module' || !isset($composer[ 'extra' ][ 'soosyze-module' ][ 'title' ])) {
                continue;
            }

            $config[ $composer[ 'extra' ][ 'soosyze-module' ][ 'title' ] ] = $composer;
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
            if (!isset($composer[ 'type' ]) || $composer[ 'type' ] !== 'soosyze-module' || !isset($composer[ 'extra' ][ 'soosyze-module' ][ 'title' ])) {
                continue;
            }

            $config[ $composer[ 'extra' ][ 'soosyze-module' ][ 'title' ] ] = $composer;
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
        if (!isset($composer[ 'extra' ][ 'soosyze-module' ]) && !is_array($composer[ 'extra' ][ 'soosyze-module' ])) {
            $errors[] = 'Les informations du module <b>' . htmlspecialchars($title) . '</b> d\'existes pas.';

            return $errors;
        }

        $validator = (new Validator())
                ->setRules([
                    'title'       => 'required|string|max:128',
                    'package'     => 'required|string|max:128',
                    'controllers' => 'required|array',
                    'icon'        => '!required|array',
                    'require'     => '!required|array'
                ])->setInputs($composer[ 'extra' ][ 'soosyze-module' ]);

        if (!$validator->isValid()) {
            $errors += $validator->getErrors();
        } elseif (empty($composer[ 'extra' ][ 'soosyze-module' ][ 'controllers' ]) || !is_array($composer[ 'extra' ][ 'soosyze-module' ][ 'controllers' ])) {
            $errors[] = 'Les informations sur les contôleurs du module <b>' . htmlspecialchars($title) . '</b> n\'existe pas.';
        } else {
            $controller = array_shift($composer[ 'extra' ][ 'soosyze-module' ][ 'controllers' ]);
            if (!class_exists($controller)) {
                $errors[] = 'Le contrôleur <b>' . htmlspecialchars($controller) . '</b>'
                    . ' du module <b>' . htmlspecialchars($title) . '</b> n\'est pas trouvé par l\'autoloader.';
            }
        }

        return $errors;
    }
}
