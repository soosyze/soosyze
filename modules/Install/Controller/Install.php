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
        "User",
        "System",
        "Node",
        "Menu",
        "Contact",
        "News"
    ];

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
            'email'           => '',
            'name'            => '',
            'firstname'       => '',
            'password'        => '',
            'password-confirm' => ''
        ];

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('install.step.check', [ ':id' => 1 ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('install-email-group', 'div', function ($form) use ($content) {
                $form->label('install-email-label', 'E-mail')
                ->email('email', 'email', [
                    'class'       => 'form-control',
                    'placeholder' => 'mon-mail@mail.com',
                    'required'    => 1,
                    'value'       => $content[ 'email' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('install-name-group', 'div', function ($form) use ($content) {
                $form->label('install-name-label', 'Nom')
                ->text('name', 'name', [
                    'class' => 'form-control',
                    'value' => $content[ 'name' ]
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('install-firstname-group', 'div', function ($form) use ($content) {
                $form->label('install-firstname-label', 'Prénom')
                ->text('firstname', 'firstname', [
                    'class' => 'form-control',
                    'value' => $content[ 'firstname' ]
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
        foreach ($this->modules as $module) {
            $obj = $module . '\Install';
            $obj = new $obj();
            $obj->install($this->container);
        }

        /* Charge les services pour utiliser les hooks d'installation. */
        $this->loadContainer();

        foreach ($this->modules as $module) {
            $obj = $module . '\Install';
            $obj = new $obj();

            if (method_exists($obj, 'hookInstall')) {
                $obj->hookInstall($this->container);
            }

            $config = Util::getJson('modules' . DS . $module . DS . 'config.json');

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
        $data = $_SESSION[ 'save' ];

        $salt = md5(time());
        self::query()->insertInto('user', [ 'email', 'password', 'salt', 'firstname',
                'name', 'actived', 'forget_pass', 'time_reset', 'time_installed', 'timezone'
            ])
            ->values([
                'email'         => $data[ 'email' ],
                'password'      => hash('sha256', $data[ 'password' ] . $salt),
                'salt'          => $salt,
                'firstname'     => $data[ 'firstname' ],
                'name'          => $data[ 'name' ],
                'actived'       => true,
                'forget_pass'    => "",
                'time_reset'     => "",
                'time_installed' => ( string ) time(),
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
        foreach ($this->modules as $module) {
            $configs = Util::getJson('modules' . DS . $module . DS . 'config.json');

            foreach ($configs as $config) {
                foreach ($config[ 'controller' ] as $controller) {
                    $obj = new $controller();

                    if (empty($obj->getPathServices())) {
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
