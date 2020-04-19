<?php

namespace SoosyzeCore\System\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Template\Template;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class Install extends \Soosyze\Controller
{
    /**
     * Liste des modules à installer.
     *
     * @var array
     */
    private $modules = [
        'Config'      => 'SoosyzeCore\\Config\\',
        'Contact'     => 'SoosyzeCore\\Contact\\',
        'Node'        => 'SoosyzeCore\\Node\\',
        'Menu'        => 'SoosyzeCore\\Menu\\',
        'System'      => 'SoosyzeCore\\System\\',
        'User'        => 'SoosyzeCore\\User\\',
        'Block'       => 'SoosyzeCore\\Block\\',
        'FileManager' => 'SoosyzeCore\\FileManager\\'
    ];

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service-install.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes-install.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/Install/';
    }

    public function index($req)
    {
        if (!($steps = $this->getSteps())) {
            return $this->get404($req);
        }
        $keys = array_keys($steps);
        if (!empty($steps[ $keys[ 0 ] ][ 'key' ])) {
            return $this->step($steps[ $keys[ 0 ] ][ 'key' ], $req);
        }

        return $this->get404($req);
    }

    public function step($id, \Soosyze\Components\Http\ServerRequest $req)
    {
        if (!($steps = $this->getSteps()) || !isset($steps[ $id ])) {
            return $this->get404($req);
        }

        $optionLang   = self::translate()->getLang();
        $optionLang['en'] = [ 'value' => 'en', 'label' => 'English' ];

        $optionTimezone = [];
        foreach (timezone_identifiers_list() as $value) {
            $optionTimezone[] = [ 'value' => $value, 'label' => $value ];
        }

        $content = [
            'lang'     => 'en',
            'timezone' => date_default_timezone_get()
                ? date_default_timezone_get()
                : 'Europe/Paris'
        ];
        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
        }

        $form = (new FormBuilder([
            'method' => 'post',
            'action' => self::router()->getRoute('install.language', [ ':id' => $id ]),
            'id'     => 'form_lang' ]))
            ->group('lang-group', 'div', function ($form) use ($content, $optionLang) {
                $form->label('lang-label', t('Language'))
                ->select('lang', $optionLang, [
                    'class'    => 'form-control',
                    'selected' => $content[ 'lang' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('timezone-group', 'div', function ($form) use ($content, $optionTimezone) {
                $form->label('timezone-label', t('Timezone'))
                ->select('timezone', $optionTimezone, [
                    'class'    => 'form-control',
                    'selected' => $content[ 'timezone' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->token('token_install');

        $messages = [
            'errors'   => [], 'warnings' => [],
            'infos'    => [], 'success'  => []
        ];
        if (isset($_SESSION[ 'messages' ][ $id ])) {
            $messages = array_merge($messages, $_SESSION[ 'messages' ][ $id ]);
            unset($_SESSION[ 'messages' ][ $id ]);
        }

        $block_page     = self::core()->callHook("step.$id", [ $id ]);
        $block_messages = (new Template('messages.php', $this->pathViews))
            ->addVars($messages);

        return (new Template('html.php', $this->pathViews))
                ->addBlock('page', $block_page)
                ->addBlock('messages', $block_messages)
                ->addVars([
                    'lang'        => $content['lang'],
                    'form'        => $form,
                    'steps'       => $steps,
                    'step_active' => $id
                ])
                ->render();
    }
    
    public function language($id, $req)
    {
        $langs     = implode(',', array_keys(self::translate()->getLang())) . ',en';
        $validator = (new Validator())
            ->setRules([
                'lang'          => 'inarray:' . $langs,
                'timezone'      => 'required|timezone',
                'token_install' => 'token'
            ])
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            $_SESSION[ 'lang' ]   = $validator->getInput('lang');
            $_SESSION[ 'inputs' ] = $validator->getInputs();
        } else {
            $_SESSION[ 'messages' ][ $id ][ 'errors' ] = $validator->getKeyErrors();
        }

        return new Redirect(self::router()->getRoute('install.step', [ ':id' => $id ]));
    }

    public function stepCheck($id, $req)
    {
        if (!($steps = $this->getSteps()) || !isset($steps[ $id ])) {
            return $this->get404($req);
        }

        /* Validation de l'étape. */
        self::core()->callHook("step.$id.check", [ $id, $req ]);

        $route = self::router()->getRoute('install.step', [ ':id' => $id ]);
        if (!empty($_SESSION[ 'inputs' ][ $id ]) && empty($_SESSION[ 'messages' ][ $id ])) {
            $this->position($steps, $id);
            if (($next = next($steps)) === false && key($steps) === null) {
                $this->installModule();

                return $this->installFinish();
            } else {
                $route = self::router()->getRoute('install.step', [ ':id' => $next[ 'key' ] ]);
            }
        }

        return new Redirect($route);
    }

    protected function getSteps()
    {
        $step = [];
        self::core()->callHook('step', [ &$step ]);
        uasort($step, function ($a, $b) {
            if ($a[ 'weight' ] === $b[ 'weight' ]) {
                return 0;
            }

            return ($a[ 'weight' ] < $b[ 'weight' ])
                ? -1
                : 1;
        });

        return $step;
    }

    private function installModule()
    {
        /* Installation */
        $instances = [];
        $profil    = $_SESSION[ 'inputs' ][ 'profil' ][ 'profil' ];
        $this->container->callHook("step.install.modules.$profil", [ &$this->modules ]);
        foreach ($this->modules as $title => $namespace) {
            $migration = $namespace . 'Installer';
            $installer = new $migration();

            $dir          = $installer->getDir();
            /* Lance les scripts d'installation (database, configuration...) */
            $installer->install($this->container);
            /* Lance les scripts de remplissages de la base de données. */
            $installer->seeders($this->container);
            $composer = Util::getJson($dir . '/composer.json');

            /* Charge le container des nouveaux services. */
            $this->loadContainer($composer);
            $composer[ 'dir' ]     = $dir;
            $instances[ $title ] = $composer;
        }

        foreach ($instances as $title => $composer) {
            self::module()->create($composer);
            /* Install les scripts de migrations. */
            $this->installMigration($composer['dir'] . DS . 'Migrations', $title);
            /* Hook d'installation pour les autres modules utilise le module actuel. */
            $this->container->callHook('install.' . $title, [ $this->container ]);
        }

        self::query()
            ->insertInto('module_require', [ 'title_module', 'title_required', 'version' ])
            ->values([ 'Core', 'System', '1.0' ])
            ->values([ 'Core', 'User', '1.0' ])
            ->execute();
    }
    
    private function installMigration($dir, $title)
    {
        if (!\is_dir($dir)) {
            return;
        }
        self::query()->insertInto('migration', [ 'migration', 'extension' ]);
        foreach (new \DirectoryIterator($dir) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            self::query()->values([
                $fileInfo->getBasename('.php'), $title
            ]);
        }
        self::query()->execute();
    }

    private function installFinish()
    {
        $save     = $_SESSION[ 'inputs' ][ 'user' ];
        $lang     = isset($_SESSION[ 'lang' ]) ? $_SESSION[ 'lang' ] : 'en';
        $timezone = isset($_SESSION[ 'timezone' ]) ? $_SESSION[ 'lang' ] : 'Europe/Paris';
        $data     = [
            'username'         => $save[ 'username' ],
            'email'            => $save[ 'email' ],
            'password'         => password_hash($save[ 'password' ], PASSWORD_DEFAULT),
            'firstname'        => $save[ 'firstname' ],
            'name'             => $save[ 'name' ],
            'actived'          => true,
            'time_reset'       => '',
            'time_installed'   => (string) time(),
            'timezone'         => $timezone,
            'rgpd'             => true,
            'terms_of_service' => true
        ];

        self::query()
            ->insertInto('user', array_keys($data))
            ->values($data)
            ->execute();

        self::query()
            ->insertInto('user_role', [ 'user_id', 'role_id' ])
            ->values([ 1, 2 ])
            ->values([ 1, 3 ])
            ->execute();

        self::config()
            ->set('settings.email', $data[ 'email' ])
            ->set('settings.time_installed', time())
            ->set('settings.lang', 'en')
            ->set('settings.timezone', $timezone)
            ->set('settings.theme', 'QuietBlue')
            ->set('settings.theme_admin', 'Admin')
            ->set('settings.logo', '')
            ->set('settings.key_cron', Util::strRandom(50))
            ->set('settings.rewrite_engine', false)
            ->set('settings.lang', $lang);

        $profil = $_SESSION[ 'inputs' ][ 'profil' ][ 'profil' ];
        $this->container->callHook("step.install.finish.$profil", [ $this->container ]);

        $path = self::config()->getPath();
        chmod($path . 'database.json', 0444);

        session_destroy();
        $route = self::router()->getBasePath();

        return new Redirect($route);
    }

    private function loadContainer($composer)
    {
        $obj  = new $composer[ 'extra' ][ 'soosyze' ][ 'controller' ]();
        if (!($path = $obj->getPathServices())) {
            return;
        }

        $this->container->addServices(Util::getJson($path));
    }

    private function position(array &$array, $position)
    {
        reset($array);
        do {
            if (key($array) === $position) {
                break;
            }
        } while (next($array));
    }
}
