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
        'Config'      => 'SoosyzeCore\Config',
        'Contact'     => 'SoosyzeCore\Contact',
        'Node'        => 'SoosyzeCore\Node',
        'News'        => 'SoosyzeCore\News',
        'Menu'        => 'SoosyzeCore\Menu',
        'System'      => 'SoosyzeCore\System',
        'User'        => 'SoosyzeCore\User'
    ];

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service-install.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing-install.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
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

        $block = (new Template('page-install.php', $this->pathViews))
            ->addVar('form', $form);

        return (new Template('html.php', $this->pathViews))
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
        /* Installation */
        $instances = [];
        foreach ($this->modules as $title => $namespace) {
            $migration = $namespace . '\Installer';
            $installer   = new $migration();
            /* Lance les scripts d'installation (database, configuration...) */
            $installer->install($this->container);
            /* Lance les scripts de remplissages de la base de données. */
            $installer->seeders($this->container);
            $composer = Util::getJson($installer->getComposer());
            /* Charge le container de nouveaux services. */
            $this->loadContainer($composer);
            $instances[$title] = ['obj' => $installer, 'composer'=> $composer];
        }

        foreach ($instances as $title => $installer) {
            self::module()->create($installer['composer']);
            /* Hook d'installation pour les autres modules utilise le module actuel. */
            $this->container->callHook(
                strtolower('install.' . self::composer()->getTitle($title)),
                [
                $this->container
            ]
            );
        }

        self::query()->insertInto('module_require', [ 'title_module', 'title_required', 'version' ])
            ->values([ 'Core', 'System', '1.0' ])
            ->values([ 'Core', 'User', '1.0' ])
            ->execute();
    }

    private function installFinish()
    {
        $save = $_SESSION[ 'save' ];
        $salt = base64_encode(random_bytes(32));
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

        self::config()->set('settings.email', $data[ 'email' ])
            ->set('settings.time_installed', time())
            ->set('settings.local', 'fr_FR')
            ->set('settings.theme', 'QuietBlue')
            ->set('settings.theme_admin', 'Admin')
            ->set('settings.logo', '')
            ->set('settings.key_cron', Util::strRandom(50))
            ->set('settings.rewrite_engine', false);
        
        $path = self::config()->getPath();
        chmod($path . 'database.json', 0444);

        unset($_SESSION[ 'save' ]);
        $route = self::router()->getBasePath();

        return new Redirect($route);
    }

    private function loadContainer($composer)
    {
        foreach ($composer[ 'extra' ][ 'soosyze-module' ][ 'controllers' ] as $controller) {
            $obj  = new $controller();
            if (!($path = $obj->getPathServices())) {
                continue;
            }

            $this->container->addServices(Util::getJson($path));
        }
    }
}
