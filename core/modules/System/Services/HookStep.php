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
            $form->label("profil-$key", function ($form) use ($key, $content, $profil) {
                $form->radio('profil', [
                    'id'       => $key,
                    'checked'  => $key === $content[ 'profil' ],
                    'required' => 1,
                    'value'    => $key,
                    'style'    => 'display:none;'
                ])->html("img_$key", '<img:attr>', [
                    'src' => $profil[ 'img' ],
                    'alt' => $profil['title']
                ]);
            }, ['class' => 'block-body']);
        }
        $form->token('token_step_install')
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
                'profil'             => 'required|inarray:' . implode(',', $profils),
                'token_step_install' => 'token'
            ])
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            $_SESSION[ 'inputs' ][ $id ] = [ 'profil' => $validator->getInput('profil') ];
        } else {
            $_SESSION[ 'inputs' ][ $id ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ $id ][ 'errors' ] = $validator->getKeyErrors();
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
            'password_confirm' => ''
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
                ->group('username-group', 'div', function ($form) use ($content) {
                    $form->label('username-label', t('User name'))
                    ->text('username', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'required'  => 1,
                        'value'     => $content[ 'username' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('email-group', 'div', function ($form) use ($content) {
                    $form->label('email-label', t('E-mail'))
                    ->email('email', [
                        'class'       => 'form-control',
                        'maxlength'   => 254,
                        'placeholder' => t('example@mail.com'),
                        'required'    => 1,
                        'value'       => $content[ 'email' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('name-group', 'div', function ($form) use ($content) {
                    $form->label('name-label', t('Last name'))
                    ->text('name', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'value'     => $content[ 'name' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('firstname-group', 'div', function ($form) use ($content) {
                    $form->label('firstname-label', t('First name'))
                    ->text('firstname', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'value'     => $content[ 'firstname' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password-group', 'div', function ($form) use ($content) {
                    $form->label('password-label', t('Password'))
                    ->password('password', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $content[ 'password' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password_confirm-group', 'div', function ($form) use ($content) {
                    $form->label('password_confirm-label', t('Confirmation of the new password'))
                    ->password('password_confirm', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $content[ 'password_confirm' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_step_install')
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
                'username'         => 'required|string|max:255|to_htmlsc',
                /* max:254 RFC5321 - 4.5.3.1.3. */
                'email'            => 'required|string|email',
                'name'             => '!required|string|max:255|to_htmlsc',
                'firstname'        => '!required|string|max:255|to_htmlsc',
                'password'         => 'required|string',
                'password_confirm' => 'required|string|equal:@password'
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
            $_SESSION[ 'messages' ][ $id ][ 'errors' ] = $validator->getKeyErrors();
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
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('features.php', $this->pathContent))->render() ])
            ->values([ (new Template('text.php', $this->pathContent))->render() ])
            ->values([ (new Template('text.php', $this->pathContent))->render() ])
            ->values([ (new Template('text.php', $this->pathContent))->render() ])
            ->values([ (new Template('about.php', $this->pathContent))->render() ])
            ->values([ t('Not Found') ])
            ->execute();
        
        $time = (string) time();
        $ci->query()
            ->insertInto('node', [
                'entity_id', 'type', 'date_created', 'date_changed', 'published', 'title'
            ])
            ->values([ 1, 'page', $time, $time, true, t('Site') ])
            ->values([ 2, 'page', $time, $time, true, t('Basic') ])
            ->values([ 3, 'page', $time, $time, true, t('Standard') ])
            ->values([ 4, 'page', $time, $time, true, t('Premium') ])
            ->values([ 5, 'page', $time, $time, true, t('About') ])
            ->values([ 6, 'page', $time, $time, true, t('Not Found') ])
            ->execute();

        $ci->query()
            ->insertInto('block', [
                'section', 'title', 'weight', 'visibility_pages', 'pages', 'content'
            ])
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
            ->insertInto('block', [ 'section', 'title', 'weight', 'content', 'hook' ])
            ->values([
                'sidebar', 'Lorem ipsum dolor', 1,
                (new Template('card_ui.php', $this->pathContent))->render(), null
            ])
            ->values([
                'sidebar', t('Archives by months'), 2, '', 'news.month'
            ])
            ->values([
                'sidebar', t('Follow us'), 1,
                (new Template('social.php', $this->pathContent))->render(), null
            ])
            ->values([
                'footer_first', t('To join us'), 1,
                (new Template('contact.php', $this->pathContent))->render(), null
            ])
            ->values([
                'footer_second', t('Access map'), 1,
                (new Template('map.php', $this->pathContent))->render(), null
            ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'menu', 'link', 'weight', 'parent', 'title_link'
            ])
            ->values([ 'node.show', 'menu-main', 'news', 2, -1, 'Blog'])
            ->values([ 'node.show', 'menu-main', 'node/7', 3, -1, 'About'])
            ->values([ 'node.show', 'menu-main', 'node/4', 3, 6, 'Basic' ])
            ->values([ 'node.show', 'menu-main', 'node/5', 3, 6, 'Standard' ])
            ->values([ 'node.show', 'menu-main', 'node/6', 3, 6, 'Premium' ])
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
            ->insertInto('block', [ 'section', 'title', 'weight', 'content', 'hook' ])
            ->values([
                'sidebar', t('Archives by months'), 1, '', 'news.month'
            ])
            ->values([
                'footer_first', t('Follow us'), 1,
                (new Template('social.php', $this->pathContent))->render(), null
            ])
            ->values([
                'footer_second', 'Lorem ipsum dolor', 1,
                (new Template('text.php', $this->pathContent))->render(), null
            ])
            ->execute();

        $ci->query()
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('about.php', $this->pathContent))->render() ])
            ->execute();
        
        $time = (string) time();
        $ci->query()
            ->insertInto('node', [
                'entity_id', 'type', 'date_created', 'date_changed', 'published', 'title'
            ])
            ->values([ 1, 'page', $time, $time, true, t('About') ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'title_link', 'link', 'menu', 'weight', 'parent'
            ])
            ->values([ 'node.show', 'About', 'node/3', 'menu-main', 3, -1 ])
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
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('about.php', $this->pathContent))->render() ])
            ->values([ (new Template('education.php', $this->pathContent))->render() ])
            ->values([ (new Template('project.php', $this->pathContent))->render() ])
            ->values([ (new Template('about.php', $this->pathContent))->render() ])
            ->values([ (new Template('about.php', $this->pathContent))->render() ])
            ->values([ (new Template('about.php', $this->pathContent))->render() ])
            ->values([ (new Template('about.php', $this->pathContent))->render() ])
            ->values([ t('Not Found') ])
            ->execute();
        
        $time = (string) time();
        $ci->query()
            ->insertInto('node', [
                'entity_id', 'type', 'date_created', 'date_changed', 'published', 'title'
            ])
            ->values([ 1, 'page', $time, $time, true, t('Site') ])
            ->values([ 2, 'page', $time, $time, true, t('Education') ])
            ->values([ 3, 'page', $time, $time, true, t('Projects') ])
            ->values([ 4, 'page', $time, $time, true, t('Project 1') ])
            ->values([ 5, 'page', $time, $time, true, t('Project 2') ])
            ->values([ 6, 'page', $time, $time, true, t('Project 3') ])
            ->values([ 7, 'page', $time, $time, true, t('Project 4') ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key',  'menu', 'link', 'weight', 'parent',  'title_link'
            ])
            ->values([ 'node.show', 'menu-main', 'node/2', 3, -1, 'Education' ])
            ->values([ 'node.show', 'menu-main', 'node/3', 3, -1, 'Projects' ])
            ->values([ 'node.show', 'menu-main', 'node/4', 4, 16, 'Project 1' ])
            ->values([ 'node.show', 'menu-main', 'node/5', 5, 16, 'Project 2' ])
            ->values([ 'node.show', 'menu-main', 'node/6', 6, 16, 'Project 3' ])
            ->values([ 'node.show', 'menu-main', 'node/7', 7, 16, 'Project 4' ])
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
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('features.php', $this->pathContent))->render() ])
            ->execute();

        $time = (string) time();
        $ci->query()
            ->insertInto('node', [
                'entity_id', 'type', 'date_created', 'date_changed', 'published', 'title'
            ])
            ->values([ 1, 'page', $time, $time, true, 'Ipsum sed adipiscing' ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'menu', 'link', 'weight', 'parent', 'title_link', 'fragment'
            ])
            ->values([ 'node.show', 'menu-main', '/', 2, -1, 'Introduction', 'text' ])
            ->values([ 'node.show', 'menu-main', '/', 3, -1, 'Features', 'features' ])
            ->values([ 'node.show', 'menu-main', '/', 5, -1, 'About', 'img' ])
            ->values([ 'node.show', 'menu-main', '/', 6, -1, 'Social media', 'social' ])
            ->execute();

        $ci->query()
            ->insertInto('block', [
                'section', 'title', 'weight', 'visibility_pages', 'pages', 'content'
            ])
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
