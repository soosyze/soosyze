<?php

namespace SoosyzeCore\System\Services;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Template\Template;
use Soosyze\Components\Validator\Validator;

class HookStep
{
    /**
     * @var \Soosyze\App
     */
    protected $core;

    /**
     * @var \Soosyze\Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $pathViews;

    /**
     * @var string
     */
    protected $pathContent;

    public function __construct($core, $router)
    {
        $this->core        = $core;
        $this->router      = $router;
        $this->pathViews   = dirname(__DIR__) . '/Views/Install/';
        $this->pathContent = dirname(__DIR__) . '/Views/Content/';
    }

    public function hookStep(&$step)
    {
        $step[ 'profil' ] = [
            'weight' => 1,
            'title'  => t('Installation profile'),
            'key'    => 'profil'
        ];
        $step[ 'user' ]   = [
            'weight' => 1,
            'title'  => t('User profile'),
            'key'    => 'user'
        ];
    }

    public function getProfils()
    {
        $assets = $this->core->getPath('modules', 'core/modules', false) . '/System/Assets/img/';
        $profil = [
            'site'      => [
                'key'         => 'site',
                'title'       => t('Website'),
                'img'         => $assets . 'site.svg',
                'description' => t('Create a standard site with all the basic features.')
            ],
            'blog'      => [
                'key'         => 'blog',
                'title'       => t('Blog'),
                'img'         => $assets . 'blog.svg',
                'description' => t('Periodically publish your tickets, articles, news...')
            ],
            'portfolio' => [
                'key'         => 'portfolio',
                'title'       => t('Portfolio'),
                'img'         => $assets . 'portfolio.svg',
                'description' => t('Highlight your skills, experiences and training.')
            ],
            'one_page'  => [
                'key'         => 'one_page',
                'title'       => t('One page'),
                'img'         => $assets . 'one_page.svg',
                'description' => t('Present your activity on a single webpage.')
            ]
        ];
        $this->core->callHook('app.step.profil', [ &$profil ]);

        return $profil;
    }

    public function hookProfil($id)
    {
        $content = [ 'profil' => '' ];

        if (isset($_SESSION[ 'inputs' ][ $id ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ][ $id ]);
        }

        $profils = $this->getProfils();
        $form    = (new FormBuilder([
            'method' => 'post',
            'action' => $this->router->getRoute('install.step.check', [ ':id' => $id ]) ]));

        foreach ($profils as $key => $profil) {
            $form->group("profil-$key", 'div', function ($form) use ($key, $content, $profil) {
                $form->radio('profil', [
                    'id'       => $key,
                    'checked'  => $key === $content[ 'profil' ],
                    'required' => 1,
                    'value'    => $key,
                    'style'    => 'display:none;'
                ])->html($key, '<img:attr:css>', [
                    'src' => $profil[ 'img' ]
                ]);
            }, [ 'class' => 'form-group' ]);
        }
        $form->token('token_install')
            ->submit('submit', t('Next'), [ 'class' => 'btn btn-success' ]);

        return (new Template('form-profil.php', $this->pathViews))->addVars([
                'form'    => $form,
                'profils' => $profils
        ]);
    }

    public function hookProfilCheck($id, $req)
    {
        $profils   = array_keys($this->getProfils());
        $validator = (new Validator())
            ->setRules([
                'profil'        => 'required|inarray:' . implode(',', $profils),
                'token_install' => 'token'
            ])
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            $_SESSION[ 'inputs' ][ $id ] = [ 'profil' => $validator->getInput('profil') ];
        } else {
            $_SESSION[ 'inputs' ][ $id ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ $id ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ][ $id ]          = $validator->getKeyInputErrors();
        }
    }

    public function hookUser($id)
    {
        $content = [
            'username'         => '',
            'email'            => '',
            'name'             => '',
            'firstname'        => '',
            'password'         => '',
            'password-confirm' => ''
        ];

        if (isset($_SESSION[ 'inputs' ][ $id ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ][ $id ]);
        }

        $form = (new FormBuilder([
            'method' => 'post',
            'action' => $this->router->getRoute('install.step.check', [ ':id' => $id ])
            ]))
            ->group('fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('legend', t('User profile'))
                ->group('install-username-group', 'div', function ($form) use ($content) {
                    $form->label('install-username-label', t('User name'))
                    ->text('username', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'required'  => 1,
                        'value'     => $content[ 'username' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('install-email-group', 'div', function ($form) use ($content) {
                    $form->label('install-email-label', t('E-mail'))
                    ->email('email', [
                        'class'       => 'form-control',
                        'maxlength'   => 254,
                        'placeholder' => t('example@mail.com'),
                        'required'    => 1,
                        'value'       => $content[ 'email' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('install-name-group', 'div', function ($form) use ($content) {
                    $form->label('install-name-label', t('Name'))
                    ->text('name', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'value'     => $content[ 'name' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('install-firstname-group', 'div', function ($form) use ($content) {
                    $form->label('install-firstname-label', t('First name'))
                    ->text('firstname', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'value'     => $content[ 'firstname' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('install-password-group', 'div', function ($form) use ($content) {
                    $form->label('install-password-label', t('Password'))
                    ->password('password', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $content[ 'password' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('install-password-confirm-group', 'div', function ($form) use ($content) {
                    $form->label('install-password-confirm-label', t('Confirmation of the new password'))
                    ->password('password-confirm', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $content[ 'password-confirm' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_install')
            ->submit('submit', t('Install'), [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors_keys' ][ $id ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ][ $id ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ][ $id ]);
        }

        return (new Template('page-install.php', $this->pathViews))
                ->addVars([ 'form' => $form ]);
    }

    public function hookUserCheck($id, $req)
    {
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
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            $_SESSION[ 'inputs' ][ $id ] = [
                'username'  => $validator->getInput('username'),
                'email'     => $validator->getInput('email'),
                'name'      => $validator->getInput('name'),
                'firstname' => $validator->getInput('firstname'),
                'password'  => $validator->getInput('password')
            ];
        } else {
            $_SESSION[ 'inputs' ][ $id ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ $id ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ][ $id ]          = $validator->getKeyInputErrors();
        }
    }

    public function hookModules(&$modules)
    {
        $modules[ 'News' ] = 'SoosyzeCore\\News\\';
    }

    public function hookSite($ci)
    {
        $ci->config()
            ->set('settings.path_index', 'node/3')
            ->set('settings.path_no_found', 'node/8')
            ->set('settings.meta_title', 'Soosyze site')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'created', 'changed', 'published', 'field'
            ])
            ->values([
                t('Home'), 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('features.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                'Basic', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('text.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                'Standard', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('text.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                'Premium', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('text.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('About'), 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('about.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('Not Found'), 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => t('Not Found')
                ])
            ])
            ->execute();

        $ci->query()
            ->insertInto('block', [ 'section', 'title', 'weight', 'visibility_pages',
                'pages', 'content' ])
            ->values([
                'header', '', 1, true, '/',
                (new Template('learn_more.php', $this->pathContent))->render()
            ])
            ->values([
                'content_header', '<span id="text">' . t('Introduction') . '</span>', 1, true, '/',
                (new Template('text.php', $this->pathContent))->render()
            ])
            ->execute();

        $ci->query()
            ->insertInto('block', [ 'section', 'title', 'weight', 'content' ])
            ->values([
                'sidebar', 'Lorem ipsum dolor', 1,
                (new Template('card_ui.php', $this->pathContent))->render()
            ])
            ->values([
                'sidebar', t('Follow us'), 1,
                (new Template('social.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_first', t('To join us'), 1,
                (new Template('contact.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_second', t('Access map'), 1,
                (new Template('map.php', $this->pathContent))->render()
            ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                'node.show', 'Blog', 'news', 'menu-main', 2, -1
            ])
            ->values([
                'node.show', 'About', 'node/7', 'menu-main', 3, -1
            ])
            ->values([
                'node.show', 'Basic', 'node/4', 'menu-main', 3, 6
            ])
            ->values([
                'node.show', 'Standard', 'node/5', 'menu-main', 3, 6
            ])
            ->values([
                'node.show', 'Premium', 'node/6', 'menu-main', 3, 6
            ])
            ->execute();
    }

    public function hookBlog($ci)
    {
        $ci->config()
            ->set('settings.path_index', 'news')
            ->set('settings.path_no_found', '')
            ->set('settings.meta_title', 'Soosyze blog')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('block', [ 'section', 'title', 'weight', 'content' ])
            ->values([
                'footer_first', t('Follow us'), 1,
                (new Template('social.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_second', 'Lorem ipsum dolor', 1,
                (new Template('text.php', $this->pathContent))->render()
            ])
            ->execute();

        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'created', 'changed', 'published', 'field'
            ])
            ->values([
                t('About'), 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('about.php', $this->pathContent))->render()
                ])
            ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                'node.show', 'About', 'node/3', 'menu-main', 3, -1
            ])
            ->execute();
    }

    public function hookPortfolio($ci)
    {
        $ci->config()
            ->set('settings.path_index', 'node/1')
            ->set('settings.path_no_found', '')
            ->set('settings.meta_title', 'Soosyze portfolio')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'created', 'changed', 'published', 'field'
            ])
            ->values([
                t('Home'), 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('about.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('Education'), 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('education.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('Projects'), 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('project.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('Project') . ' 1', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('about.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('Project') . ' 2', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('about.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('Project') . ' 3', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('about.php', $this->pathContent))->render()
                ])
            ])
            ->values([
                t('Project') . ' 4', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => (new Template('about.php', $this->pathContent))->render()
                ])
            ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([
                'node.show', 'Education', 'node/2', 'menu-main', 3, -1
            ])
            ->values([
                'node.show', 'Projects', 'node/3', 'menu-main', 3, -1
            ])
            ->values([
                'node.show', 'Project' . ' 1', 'node/4', 'menu-main', 4, 15
            ])
            ->values([
                'node.show', 'Project' . ' 2', 'node/5', 'menu-main', 5, 15
            ])
            ->values([
                'node.show', 'Project' . ' 3', 'node/6', 'menu-main', 6, 15
            ])
            ->values([
                'node.show', 'Project' . ' 4', 'node/7', 'menu-main', 7, 15
            ])
            ->execute();

        $ci->query()
            ->insertInto('block', [ 'section', 'title', 'weight', 'content' ])
            ->values([
                'sidebar', t('About'), 1,
                (new Template('card_ui.php', $this->pathContent))->render()
            ])
            ->values([
                'sidebar', t('Follow us'), 1,
                (new Template('social.php', $this->pathContent))->render()
            ])
            ->execute();
    }

    public function hookOnePage($ci)
    {
        $ci->config()
            ->set('settings.path_index', 'node/1')
            ->set('settings.path_no_found', '')
            ->set('settings.meta_title', 'Soosyze One page')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'created', 'changed', 'published', 'field'
            ])
            ->values([
                'Ipsum sed adipiscing', 'page', (string) time(), (string) time(),
                true,
                serialize([
                    'body' => (new Template('features.php', $this->pathContent))->render()
                ])
            ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'title_link', 'link', 'fragment', 'menu', 'weight', 'parent'
            ])
            ->values([
                'node.show', 'Introduction', '/', 'text', 'menu-main',
                2, -1
            ])
            ->values([
                'node.show', 'Features', '/', 'features', 'menu-main',
                3, -1
            ])
            ->values([
                'node.show', 'About', '/', 'img', 'menu-main',
                5, -1
            ])
            ->values([
                'node.show', 'Social media', '/', 'social', 'menu-main',
                6, -1
            ])
            ->execute();

        $ci->query()
            ->insertInto('block', [ 'section', 'title', 'weight', 'visibility_pages',
                'pages', 'content' ])
            ->values([
                'header', '', 1, true, '/',
                (new Template('learn_more.php', $this->pathContent))->render()
            ])
            ->values([
                'content_header', '<span id="text">' . t('Introduction') . '</span>', 1, true,
                '/',
                (new Template('text.php', $this->pathContent))->render()
            ])
            ->values([
                'content_footer', '<span id="img">' . t('About') . '</span>', 1, true, '/',
                (new Template('img.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_first', 'Lorem ipsum', 1, false, 'admin/%' . PHP_EOL . 'user/%',
                (new Template('text.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_second', '<span id="social">' . t('Follow us') . '</span>', 2, true,
                '/',
                (new Template('social.php', $this->pathContent))->render()
            ])
            ->execute();
    }
}
