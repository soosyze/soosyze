<?php

namespace System\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

define('VIEWS_SYSTEM', MODULES_CORE . 'System' . DS . 'Views' . DS);
define('CONFIG_SYSTEM', MODULES_CORE . 'System' . DS . 'Config' . DS);

class ModulesManager extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = CONFIG_SYSTEM . 'service.json';
        $this->pathRoutes   = CONFIG_SYSTEM . 'routing.json';
    }

    public function edit($r)
    {
        /* Récupère les modules en base de données. */
        $content = self::module()->listModuleActive();

        /* Récupère tous les fichiers de configuration. */
        $config = self::module()->getConfigAll();

        $action = self::router()->getRoute('system.module.update');
        $form   = new FormBuilder([ 'method' => 'post', 'action' => $action ]);

        foreach ($config as $key => $values) {
            $attr              = [];
            /* Si le module est présent en base de données alors il est installé. */
            $attr[ 'checked' ] = isset($content[ $key ]);

            /* Si le module est activé est qu'il est requis. */
            $isRequiredForModule = [];
            if ($this->isDisabled($key, $isRequiredForModule)) {
                $attr[ 'disabled' ] = 'disabled';
            }

            /* Si un des module requis est non installé. */
            $isRequired = [];
            foreach ($values[ 'required' ] as $require) {
                if (!isset($content[ $require ])) {
                    $isRequired         = $values[ 'required' ];
                    $attr[ 'disabled' ] = 'disabled';
                }
            }

            $form->checkbox($key, $key, $attr)
                ->label('module-' . $key, '<span class="ui"></span> ' . $key, [
                    'for' => $key
            ]);

            $package[ $values[ 'package' ] ][ $key ] = [
                'name'                => $key,
                'description'         => $values[ 'description' ],
                'version'             => $values[ 'version' ],
                'isRequired'          => $isRequired,
                'isRequiredForModule' => $isRequiredForModule
            ];
        }

        $form->token()->submit('submit', 'Enregistrer');

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-th-large" aria-hidden="true"></i> Modules'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-modules.php', VIEWS_SYSTEM, [
                    'form'    => $form,
                    'package' => $package
        ]);
    }

    public function update($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->addInput('token', $post[ 'token' ])
            ->addRule('token', 'token');

        unset($post[ 'token' ], $post[ 'submit' ]);
        foreach ($post as $key => $value) {
            $validator->addRule($key, 'bool')
                ->addInput($key, $value);
        }

        if ($validator->isValid()) {
            $data = $validator->getInputs();
            unset($data[ 'token' ]);

            $module_active = array_flip(self::module()->listModuleActiveNotRequire());
            $this->uninstallModule($module_active, $data);
            $this->installModule($module_active, $data);

            $_SESSION[ 'messages' ][ 'success' ] = [ 'Configuration Enregistrée' ];
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        }

        $route = self::router()->getRoute('system.module.edit');

        return new Redirect($route);
    }

    private function installModule($module_active, $data)
    {
        /* Si il y a des modules en plus, alors il seront installés. */
        $diff = array_diff_key($data, $module_active);

        if ($diff) {
            foreach ($diff as $key => $value) {
                /* Instantie et exécute le service d'installation. */
                $obj = $key . '\Install';
                $obj = new $obj();
                $obj->install($this->container);

                /* Hook d'installation pour que le module utilise les autres modules. */
                if (method_exists($obj, 'hookInstall')) {
                    $obj->hookInstall($this->container);
                }

                /* Récupère les configurations et les sauvegardes dans la table module */
                $config = self::module()->getConfig($key);
                self::module()->create($config);

                /* Hook d'installation pour les autres modules utilise le module actuel. */
                $this->container->callHook(strtolower('install.' . $key), [ $this->container ]);
            }
        }
    }

    private function uninstallModule($module_active, $data)
    {
        /* Si modules en moins alors désinstalle */
        $diff = array_diff_key($module_active, $data);

        if ($diff) {
            foreach ($diff as $key => $value) {
                /* Instantie et exécute le service désinstallation. */
                $obj = $key . '\Install';
                if (!class_exists($obj)) {
                    continue;
                }

                $obj = new $obj();
                $obj->uninstall($this->container);
                /* Supprime le module à partir de son nom */
                self::module()->uninstallModule($key);
            }
        }
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
        if (($isRequiredForModule = self::module()->isRequiredCore($key))) {
            return true;
        }

        /* Si le module est activé est qu'il est requis. */
        if (($isRequiredForModule = self::module()->isRequiredForModule($key))) {
            return true;
        }

        return false;
    }
}
