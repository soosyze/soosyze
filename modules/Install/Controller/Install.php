<?php

namespace Install\Controller;

use Soosyze\Components\Util\Util;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Template\Template;

define("VIEWS_INSTALL", MODULES_CORE . 'Install' . DS . 'Views' . DS);
define("CONFIG_INSTALL", MODULES_CORE . 'Install' . DS . 'Config' . DS);

class Install extends \Soosyze\Controller
{
    protected $pathServices = CONFIG_INSTALL . 'service.json';

    protected $pathRoutes = CONFIG_INSTALL . 'routing.json';
    /**
     * Liste des modules à installer.
     *
     * @var array
     */
    private $modules = [
        "User"    => 'User\\Controller\\User',
        "System"  => 'System\\Controller\\System',
        "Node"    => 'Node\\Controller\\Node',
        "Menu"    => 'Menu\\Controller\\Menu',
        "Contact" => 'Contact\\Controller\\Contact',
        "News"    => 'New\\Controller\\NewsController'
    ];

    /**
     * Configuration d'installation.
     *
     * @var array
     */
    private $settings = [];

    public function step($id)
    {
        switch ($id) {
            case 2:
                $this->installModule();

                return $this->installFinish();
        }
    }

    public function stepCheck($id, $r)
    {
        switch ($id) {
            case 1:
                return $this->installUserCheck($r);
        }
    }

    public function index()
    {
        $content = [
            'email'           => '',
            'name'            => '',
            'firstname'       => '',
            'password'        => '',
            'passwordConfirm' => ''
        ];

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('install.step.check', [ ':id' => 1 ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('group-email', 'div', function ($form) use ($content) {
                $form->label('label-email', 'Email', [ 'class' => 'control-label' ])
                ->email('email', 'email', [
                    'class'       => 'form-control',
                    'required'    => 1,
                    'value'       => $content[ 'email' ],
                    'placeholder' => 'mon-mail@mail.com'
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('group-name', 'div', function ($form) use ($content) {
                $form->label('label-name', 'Nom', [ 'class' => 'control-label' ])
                ->text('name', 'name', [
                    'value' => $content[ 'name' ],
                    'class' => 'form-control'
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('group-firstname', 'div', function ($form) use ($content) {
                $form->label('label-firstname', 'Prénom', [ 'class' => 'control-label' ])
                ->text('firstname', 'firstname', [
                    'value' => $content[ 'firstname' ],
                    'class' => 'form-control'
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('group-password', 'div', function ($form) use ($content) {
                $form->label('label-password', 'Mot de passe')
                ->password('password', 'password', [
                    'required' => 1,
                    'value'    => $content[ 'password' ],
                    'class'    => 'form-control'
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('group-password-confirm', 'div', function ($form) use ($content) {
                $form->label('label-password-confirm', 'Confirmation du mot de passe')
                ->password('password-confirm', 'password-confirm', [
                    'required' => 1,
                    'value'    => $content[ 'passwordConfirm' ],
                    'class'    => 'form-control'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token()
            ->submit('submit', 'Installer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }
        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        $block = (new Template('installUser.php', VIEWS_INSTALL))
            ->addVar('form', $form);

        return (new Template('install.php', VIEWS_INSTALL))
                ->addBlock('page', $block)
                ->render();
    }

    private function installUserCheck($r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email'            => 'required|email|htmlsc',
                'name'             => '!required',
                'firstname'        => '!required',
                'password'         => 'required|string',
                'password-confirm' => 'required|string|equal:@password'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            /* N'enregistre pas le token de sécurité dans la bdd. */
            $data = $validator->getInputs();
            unset($data[ 'token' ], $data[ 'submit' ]);

            $_SESSION[ 'save' ] = $data;

            $route = self::router()->getRoute('install.step', [ ':id' => 2 ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = $validator->getInputs();
        $_SESSION[ 'errors' ]      = $validator->getErrors();
        $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();

        $route = self::router()->getRoute('install.index');

        return new Redirect($route);
    }

    private function installModule()
    {
        foreach ($this->modules as $key => $module) {
            $obj = $key . '\Install';
            $obj = new $obj();
            call_user_func([ $obj, 'install' ], $this->container);
        }

        /* Charge les services pour utiliser les hooks d'installation. */
        $this->loadContainer();

        foreach ($this->modules as $key => $module) {
            $obj = $key . '\Install';
            $obj = new $obj();

            if (method_exists($obj, 'hookInstall')) {
                call_user_func([ $obj, 'hookInstall' ], $this->container);
            }

            $config = Util::getJson('modules' . DS . $key . DS . 'config.json');

            foreach ($config as $conf) {
                $requires = $conf[ 'required' ];
                unset($conf[ 'required' ]);

                self::query()
                    ->insertInto('module', [ 'name', 'controller', 'version', 'description',
                        'package', 'locked' ])
                    ->values($conf)
                    ->execute();

                foreach ($requires as $require) {
                    self::query()
                        ->insertInto('module_required', [ 'name_module', 'name_required' ])
                        ->values([ $conf[ 'name' ], $require ])
                        ->execute();
                }
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
        $data = $_SESSION[ 'save' ];

        $salt = md5(time());
        self::query()->insertInto('user', [ 'email', 'password', 'salt', 'firstname',
                'name', 'actived', 'forgetPass', 'timeReset', 'timeInstalled', 'timezone'
            ])
            ->values([
                'email'         => $data[ 'email' ],
                'password'      => hash('sha256', $data[ 'password' ] . $salt),
                'salt'          => $salt,
                'firstname'     => $data[ 'firstname' ],
                'name'          => $data[ 'name' ],
                'actived'       => true,
                'forgetPass'    => "",
                'timeReset'     => "",
                'timeInstalled' => ( string ) time(),
                'timezone'      => "Europe/Paris"
            ])
            ->execute();

        self::query()->insertInto('user_role', [ 'user_id', 'role_id' ])
            ->values([ 1, 2 ])
            ->values([ 1, 3 ])
            ->execute();
        
        self::config()->set('settings.email', $data[ 'email' ]);
        self::config()->set('settings.time_installed', time());
        self::config()->set('settings.local', 'fr_FR');
        self::config()->set('settings.theme', 'Bootstrap 3');
  
        unset($_SESSION[ 'save' ]);
        $route = self::router()->getBasePath();

        return new Redirect($route);
    }

    private function loadContainer()
    {
        $services = [];
        foreach ($this->modules as $key => $module) {
            $configs = Util::getJson('modules' . DS . $key . DS . 'config.json');

            foreach ($configs as $config) {
                $obj = new $config[ 'controller' ]();

                if (empty($obj->getPathServices())) {
                    continue;
                }

                $servicesConfig = Util::getJson($obj->getPathServices());
                foreach ($servicesConfig as $key => $value) {
                    $this->container->setService($key, $value);
                    $services[ $key ] = $value;
                }
            }
        }

        return $services;
    }
}
