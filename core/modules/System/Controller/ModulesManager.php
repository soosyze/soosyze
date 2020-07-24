<?php

namespace SoosyzeCore\System\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class ModulesManager extends \Soosyze\Controller
{
    protected $pathViews;

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function edit($r)
    {
        /* Récupère les modules en base de données. */
        $data = self::module()->listModuleActive();

        /* Récupère tous les fichiers de configuration. */
        $composer = self::composer()->getAllComposer();

        $action = self::router()->getRoute('system.module.update');
        $form   = new FormBuilder([ 'method' => 'post', 'action' => $action ]);

        foreach ($composer as $values) {
            $module = $values[ 'extra' ][ 'soosyze' ];
            $title  = htmlspecialchars($module[ 'title' ]);

            $attr              = [];
            /* Si le module est présent en base de données alors il est installé. */
            $attr[ 'checked' ] = isset($data[ $title ]);

            /* Si le module est activé est qu'il est requis. */
            $isRequiredForModule = [];
            if ($this->isDisabled($title, $isRequiredForModule)) {
                $attr[ 'disabled' ] = 'disabled';
            }

            /* Si un des module requis est non installé. */
            $isRequired = [];
            if (isset($module[ 'require' ])) {
                foreach ($module[ 'require' ] as $require => $version) {
                    if (!isset($data[ $require ])) {
                        $isRequired[]       = htmlspecialchars($require);
                        $attr[ 'disabled' ] = 'disabled';
                    } elseif (!self::composer()->validVersion($version, $data[ $require ][ 'version' ], true)) {
                        $isRequired[]       = htmlspecialchars($require . " (v$version)");
                        $attr[ 'disabled' ] = 'disabled';
                    }
                }
            }

            $form->checkbox("modules[$title]", $attr)
                ->label($title, '<span class="ui"></span> ' . $title, [
                    'for' => "modules[$title]"
            ]);

            $packages[ htmlspecialchars($module[ 'package' ]) ][ $title ] = [
                'icon'                => [
                    'name'             => isset($module[ 'icon' ][ 'name' ])
                        ? htmlspecialchars($module[ 'icon' ][ 'name' ])
                        : 'fas fa-puzzle-piece',
                    'background-color' => isset($module[ 'icon' ][ 'background-color' ])
                        ? htmlspecialchars($module[ 'icon' ][ 'background-color' ])
                        : '#ddd',
                    'color'            => isset($module[ 'icon' ][ 'color' ])
                        ? htmlspecialchars($module[ 'icon' ][ 'color' ])
                        : '#666'
                ],
                'title'               => $title,
                'description'         => isset($values[ 'description' ])
                    ? htmlspecialchars($values[ 'description' ])
                    : null,
                'isRequired'          => $isRequired,
                'isRequiredForModule' => $isRequiredForModule,
                'version'             => isset($values[ 'version' ])
                    ? htmlspecialchars($values[ 'version' ])
                    : null,
                'support'             => isset($values[ 'support' ][ 'docs' ])
                    ? htmlspecialchars($values[ 'support' ][ 'docs' ])
                    : null
            ];
        }
        $form->token('token_module_edit')
            ->submit('submit', t('Save'));

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        ksort($packages);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-th-large" aria-hidden="true"></i>',
                    'title_main' => t('Modules')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-modules.php', $this->pathViews, [
                    'module_update'      => self::config()->get('settings.module_update'),
                    'count'              => count($composer),
                    'link_module_check'  => self::router()->getRoute('system.module.check'),
                    'link_module_update' => self::router()->getRoute('system.module.updater'),
                    'form'               => $form,
                    'packages'           => $packages
        ]);
    }

    public function update($req)
    {
        $route     = self::router()->getRoute('system.module.edit');
        $validator = (new Validator())
            ->setRules([
                'modules'           => '!required|array',
                'token_module_edit' => 'token'
            ])
            ->setInputs($req->getParsedBody());

        if (!$validator->isValid()) {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

            return new Redirect($route);
        }

        $data = $validator->getInput('modules', []);

        $moduleActive = array_flip(self::module()->listModuleActiveNotRequire());

        $outUninstall = $this->uninstallModule($moduleActive, $data);
        $outInstall   = $this->installModule($moduleActive, $data);

        if (empty($outInstall) && empty($outUninstall)) {
            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $outInstall + $outUninstall;
        }

        return new Redirect($route);
    }

    private function installModule($moduleActive, $data)
    {
        /* S'il n'y a pas de modules à installer. */
        if (!($diff = array_diff_key($data, $moduleActive))) {
            return [];
        }

        $composer = self::composer()->getAllComposer();
        $errors   = [];
        $modules  = array_keys($diff);

        foreach ($modules as $title) {
            /* Vérifie que le module existe. */
            if (!isset($composer[ $title ])) {
                /* Installation d'un module non existant. */
                $errors[] = t('The :title module does not exist.', [ ':title' => $title ]);
            }
            /* Vérifie que le fichier composer n'est pas corrompu. */
            elseif ($out = self::composer()->validComposer($title, $composer[ $title ])) {
                $errors += $out;
            }
            /* Vérifie s'il a des modules qu'il requit et si leur version est conforme. */
            elseif ($out = self::composer()->validRequireModule($title, $composer)) {
                $errors += $out;
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        /* Installation */
        foreach ($modules as $title) {
            $migration = self::composer()->getNamespace($title) . 'Installer';
            $installer = new $migration();

            $installer->boot();
            /* Lance les scripts d'installation (database, configuration...) */
            $installer->install($this->container);
            /* Lance les scripts de remplissages de la base de données. */
            $installer->seeders($this->container);
            /* Lance l'installation des hooks déjà présents. */
            $installer->hookInstall($this->container);
            /* Charge le container de nouveaux services. */
            $this->loadContainer($composer[ $title ]);

            $composer[ $title ] += [
                'dir'          => $installer->getDir(),
                'translations' => $installer->getTranslations()
            ];
        }

        self::module()->loadTranslations($modules, $composer, true);

        /* Lance l'installation des hooks présents dans les modules nouvellement installés. */
        foreach ($modules as $title) {
            /* Enregistre le module en base de données. */
            self::module()->create($composer[ $title ]);
            /* Install les scripts de migrations. */
            self::migration()->installMigration(
                $composer[ $title ][ 'dir' ] . DS . 'Migrations',
                $title
            );
            
            $this->container->callHook('install.' . $title, [ $this->container ]);
        }

        return [];
    }

    private function uninstallModule($moduleActive, $data)
    {
        /* S'il n'y a pas des modules à désinstaller. */
        if (!($diff = array_diff_key($moduleActive, $data))) {
            return [];
        }

        $composer = self::composer()->getAllComposer();
        $errors   = [];
        $modules  = array_keys($diff);

        foreach ($modules as $title) {
            /* Vérifie que le module existe. */
            if (!isset($composer[ $title ])) {
                /* Dé-installation d'un module non existant. */
                $errors[] = t('The :title module does not exist.', [ ':title' => $title ]);
            }
            /* Vérifie que le fichier composer n'est pas corrompu. */
            elseif ($out = self::composer()->validComposer($title, $composer[ $title ])) {
                $errors += $out;
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        $instances = [];
        foreach ($modules as $title) {
            $migration           = self::composer()->getNamespace($title) . 'Installer';
            $installer           = new $migration();
            $instances[ $title ] = $installer;
            /* Supprime le module à partir de son nom. */
            self::module()->uninstallModule($title);
            /* Lance les scripts de dé-installation (database, configuration...). */
            $installer->uninstall($this->container);
        }

        foreach ($instances as $title => $installer) {
            $installer->hookUninstall($this->container);
            self::migration()->uninstallMigration($title);
            $this->container->callHook('uninstall.' . $title, [ $this->container ]);
        }

        return [];
    }
    
    /**
     * Si un module installé est requis par d'autre module.
     *
     * @param string $key     Nom du module à désactiver.
     * @param array  $modules Liste des modules requis par le module.
     *
     * @return bool
     */
    private function isDisabled($key, array &$isRequiredForModule)
    {
        /* Si le module est requis par le core. */
        if ($isRequiredForModule = self::module()->isRequiredCore($key)) {
            return true;
        }

        /* Si le module est activé est qu'il est requis. */
        if ($isRequiredForModule = self::module()->isRequiredForModule($key)) {
            return true;
        }

        return false;
    }

    private function loadContainer($composer)
    {
        $obj  = new $composer[ 'extra' ][ 'soosyze' ][ 'controller' ]();
        if (!($path = $obj->getPathServices())) {
            return;
        }

        $this->container->addServices(Util::getJson($path));
    }
}
