<?php

namespace Install\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Template\Template;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

define('VIEWS_INSTALL', MODULES_CORE . 'Install' . DS . 'Views' . DS);
define('CONFIG_INSTALL', MODULES_CORE . 'Install' . DS . 'Config' . DS);

class Install extends \Soosyze\Controller
{
    /**
     * Liste des modules à installer.
     *
     * @var array
     */
    private $modules = [
        'User',
        'System',
        'Node',
        'Menu',
        'Contact',
        'News'
    ];

    public function __construct()
    {
        $this->pathServices = CONFIG_INSTALL . 'service.json';
        $this->pathRoutes   = CONFIG_INSTALL . 'routing.json';
    }

    public function step($id)
    {
        switch ($id) {
            case 2:
                $this->installModule();

                return $this->installFinish();
        }
    }

    public function stepCheck($id, $req)
    {
        switch ($id) {
            case 1:
                return $this->installUserCheck($req);
        }
    }

    public function index()
    {
        $content = [
            'username'         => '',
            'email'            => '',
            'name'             => '',
            'firstname'        => '',
            'password'         => '',
            'password-confirm' => ''
        ];

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('install.step.check', [ ':id' => 1 ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('install-username-group', 'div', function ($form) use ($content) {
                $form->label('install-username-label', 'Nom utilisateur')
                ->text('username', 'username', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'required'  => 1,
                    'value'     => $content[ 'username' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('install-email-group', 'div', function ($form) use ($content) {
                $form->label('install-email-label', 'E-mail')
                ->email('email', 'email', [
                    'class'       => 'form-control',
                    'maxlength'   => 254,
                    'placeholder' => 'email@exemple.com',
                    'required'    => 1,
                    'value'       => $content[ 'email' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('install-name-group', 'div', function ($form) use ($content) {
                $form->label('install-name-label', 'Nom')
                ->text('name', 'name', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $content[ 'name' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('install-firstname-group', 'div', function ($form) use ($content) {
                $form->label('install-firstname-label', 'Prénom')
                ->text('firstname', 'firstname', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $content[ 'firstname' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('install-password-group', 'div', function ($form) use ($content) {
                $form->label('install-password-label', 'Mot de passe')
                ->password('password', 'password', [
                    'class'    => 'form-control',
                    'required' => 1,
                    'value'    => $content[ 'password' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('install-password-confirm-group', 'div', function ($form) use ($content) {
                $form->label('install-password-confirm-label', 'Confirmation du mot de passe')
                ->password('password-confirm', 'password-confirm', [
                    'class'    => 'form-control',
                    'required' => 1,
                    'value'    => $content[ 'password-confirm' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->token()
            ->submit('submit', 'Installer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        $block = (new Template('installUser.php', VIEWS_INSTALL))
            ->addVar('form', $form);

        return (new Template('install.php', VIEWS_INSTALL))
                ->addBlock('page', $block)
                ->render();
    }

    private function installUserCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'username'         => 'required|string|max:255|htmlsc',
                /* max:254 RFC5321 - 4.5.3.1.3. */
                'email'            => 'required|email|max:254|htmlsc',
                'name'             => '!required|string|max:255|htmlsc',
                'firstname'        => '!required|string|max:255|htmlsc',
                'password'         => 'required|string',
                'password-confirm' => 'required|string|equal:@password'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $_SESSION[ 'save' ] = [
                'username'  => $validator->getInput('username'),
                'email'     => $validator->getInput('email'),
                'name'      => $validator->getInput('name'),
                'firstname' => $validator->getInput('firstname'),
                'password'  => $validator->getInput('password'),
            ];

            $route = self::router()->getRoute('install.step', [ ':id' => 2 ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = $validator->getInputs();
        $_SESSION[ 'errors' ]      = $validator->getErrors();
        $_SESSION[ 'errors_keys' ] = $validator->getKeyInputErrors();

        $route = self::router()->getRoute('install.index');

        return new Redirect($route);
    }

    private function installModule()
    {
        foreach ($this->modules as $module) {
            $obj = $module . '\Install';
            $obj = new $obj();
            $obj->install($this->container);
        }

        /* Charge les services pour utiliser les hooks d'installation. */
        $this->loadContainer();
        $pathModules = self::core()->getSetting('modules');

        foreach ($this->modules as $module) {
            $obj = $module . '\Install';
            $obj = new $obj();

            if (method_exists($obj, 'hookInstall')) {
                $obj->hookInstall($this->container);
            }

            $config = Util::getJson($pathModules . $module . DS . 'config.json');

            foreach ($config as $conf) {
                $this->container->get('module')->create($conf);
            }
        }

        self::query()
            ->insertInto('module_required', [ 'name_module', 'name_required' ])
            ->values([ 'Core', 'System' ])
            ->execute();
        self::query()
            ->insertInto('module_required', [ 'name_module', 'name_required' ])
            ->values([ 'Core', 'ModulesManager' ])
            ->execute();
        self::query()
            ->insertInto('module_required', [ 'name_module', 'name_required' ])
            ->values([ 'Core', 'User' ])
            ->execute();
    }

    private function installFinish()
    {
        $save = $_SESSION[ 'save' ];
        $salt = md5(time());
        $data = [
            'username'       => $save[ 'username' ],
            'email'          => $save[ 'email' ],
            'password'       => password_hash(hash('sha256', $save[ 'password' ] . $salt), PASSWORD_DEFAULT),
            'salt'           => $salt,
            'firstname'      => $save[ 'firstname' ],
            'name'           => $save[ 'name' ],
            'actived'        => true,
            'time_reset'     => '',
            'time_installed' => (string) time(),
            'timezone'       => 'Europe/Paris'
        ];
        self::query()->insertInto('user', array_keys($data))
            ->values($data)
            ->execute();

        self::query()->insertInto('user_role', [ 'user_id', 'role_id' ])
            ->values([ 1, 2 ])
            ->values([ 1, 3 ])
            ->execute();

        self::config()->set('settings.email', $data[ 'email' ]);
        self::config()->set('settings.time_installed', time());
        self::config()->set('settings.local', 'fr_FR');
        self::config()->set('settings.theme', 'QuietBlue');
        self::config()->set('settings.theme_admin', 'Admin');
        self::config()->set('settings.logo', '');

        $path = self::config()->getPath();
        chmod($path . 'database.json', 0444);

        unset($_SESSION[ 'save' ]);
        $route = self::router()->getBasePath();

        return new Redirect($route);
    }

    private function loadContainer()
    {
        $pathModules = self::core()->getSetting('modules');
        foreach ($this->modules as $module) {
            $configs = Util::getJson($pathModules . $module . DS . 'config.json');

            foreach ($configs as $config) {
                foreach ($config[ 'controller' ] as $controller) {
                    $obj = new $controller();

                    if (!$obj->getPathServices()) {
                        continue;
                    }

                    $servicesConfig = Util::getJson($obj->getPathServices());
                    foreach ($servicesConfig as $key => $value) {
                        $args = isset($value[ 'arguments' ])
                            ? $value[ 'arguments' ]
                            : [];
                        $this->container->setService($key, $value[ 'class' ], $args);
                    }
                }
            }
        }
    }
}
