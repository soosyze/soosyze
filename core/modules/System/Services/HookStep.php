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

    /**
     * @var string[]
     */
    protected static $columnsMenu = [
        'key', 'icon', 'title_link', 'link', 'link_router', 'menu', 'weight', 'parent'
    ];

    public function __construct($core, $router)
    {
        $this->core        = $core;
        $this->router      = $router;
        $this->pathViews   = dirname(__DIR__) . '/Views/system/';
        $this->pathContent = dirname(__DIR__) . '/Views/install/';
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

        foreach (array_keys($profils) as $key) {
            $form->group("profil_$key-group", 'div', function ($form) use ($key, $content) {
                $form->radio('profil', [
                    'id'      => "profil_$key",
                    'checked' => $key === $content[ 'profil' ],
                    'value'   => $key
                ])->label("$key-label", t('Select'), [
                    'for' => "profil_$key"
                ]);
            }, [ 'class' => 'radio-button' ]);
        }
        $form->token('token_step_install')
            ->submit('submit', t('Next'), [ 'class' => 'btn btn-success' ]);

        return (new Template('content-install-form_profil.php', $this->pathViews))->addVars([
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
            $form->addAttrs($_SESSION[ 'errors_keys' ][ $id ], [ 'class' => 'is-invalid' ]);
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
        $this->ci = $ci;
        $ci->config()
            ->set('settings.path_index', 'node/3')
            ->set('settings.path_no_found', 'node/8')
            ->set('settings.meta_title', 'Soosyze site')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('block-features.php', $this->pathContent))->render() ])
            ->values([ (new Template('block-text.php', $this->pathContent))->render() ])
            ->values([ (new Template('block-text.php', $this->pathContent))->render() ])
            ->values([ (new Template('block-text.php', $this->pathContent))->render() ])
            ->values([ (new Template('page-about.php', $this->pathContent))->render() ])
            ->values([ t('Not Found') ])
            ->execute();

        $time = (string) time();
        $ci->query()
            ->insertInto('node', [
                'entity_id', 'type', 'date_created', 'date_changed', 'node_status_id',
                'title'
            ])
            ->values([ 1, 'page', $time, $time, 1, t('Site') ]) // id = 3
            ->values([ 2, 'page', $time, $time, 1, t('Basic') ])
            ->values([ 3, 'page', $time, $time, 1, t('Standard') ])
            ->values([ 4, 'page', $time, $time, 1, t('Premium') ])
            ->values([ 5, 'page', $time, $time, 1, t('About') ])
            ->values([ 6, 'page', $time, $time, 1, t('Not Found') ])
            ->execute();

        $ci->query()
            ->insertInto('system_alias_url', [ 'source', 'alias' ])
            ->values([ 'node/3', 'page/site' ])
            ->values([ 'node/4', 'page/basic' ])
            ->values([ 'node/5', 'page/standard' ])
            ->values([ 'node/6', 'page/premium' ])
            ->values([ 'node/7', 'page/about' ])
            ->execute();

        $idMenuBasic    = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', 'fa fa-bolt', 'Basic', 'page/basic', 'node/4', 'menu-main', 1, 7
        ]);
        $idMenuStandard = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', 'fa fa-anchor', 'Standard', 'page/standard', 'node/5', 'menu-main',
            2, 7
        ]);
        $idMenuPremium  = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', 'fa fa-gem', 'Premium', 'page/premium', 'node/6', 'menu-main', 3, 7
        ]);
        $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'Blog', 'news', 'menu-main', 'news', 2, -1
        ]);
        $idMenuAbout    = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'About', 'page/about', 'menu-main', 'node/7', 3, -1
        ]);
        
        $ci->query()->update('menu_link', [ 'has_children' => true ])->where('id', 7)->execute();

        $ci->query()->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
            ->values([ 4, $idMenuBasic ])
            ->values([ 5, $idMenuStandard ])
            ->values([ 6, $idMenuPremium ])
            ->values([ 7, $idMenuAbout ])
            ->execute();

        /* Block content */
        $ci->query()
            ->insertInto('block', [
                'section', 'title',
                'weight',
                'visibility_pages', 'pages',
                'content'
            ])
            ->values([
                'header', '',
                1,
                true, '/',
                (new Template('block-learn_more.php', $this->pathContent))->render()
            ])
            ->values([
                'content_header', '<span id="text">' . t('Introduction') . '</span>',
                1,
                true, '/',
                (new Template('block-text.php', $this->pathContent))->render()
            ])
            ->values([
                'sidebar', '',
                1,
                true, '/',
                (new Template('block-about.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_first', t('To join us'),
                1,
                true, 'contact',
                (new Template('block-contact.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_second', t('Access map'),
                1,
                true, 'contact',
                (new Template('block-map.php', $this->pathContent))->render()
            ])
            ->execute();

        /* block hook. */
        $ci->query()
            ->insertInto('block', [
                'section', 'title',
                'weight',
                'hook', 'key_block',
                'options',
                'visibility_pages', 'pages'
            ])
            ->values([
                'content_footer', t('Last News'),
                1,
                'news.last', 'news.last',
                json_encode([ 'limit' => 3, 'offset' => 0, 'more' => true ]),
                true, '/'
            ])
            ->values([
                'content_footer', '',
                2,
                'node.next_previous', 'node.next_previous',
                json_encode([
                    'display'       => 'meta-title',
                    'next_text'     => 'Next :node_type_name',
                    'previous_text' => 'Previous :node_type_name',
                    'type'          => 'article'
                ]),
                true, 'news/%/%/%/%'
            ])
            ->values([
                'sidebar', '',
                1,
                'social', 'social',
                '',
                true, '/' . PHP_EOL . 'news'
            ])
            ->values([
                'footer', '',
                1,
                'social', 'social',
                '',
                false, '/' . PHP_EOL . 'news' . PHP_EOL . 'admin/%' . PHP_EOL . 'user/%'
            ])
            ->values([
                'sidebar', t('Archives by months'),
                2,
                'news.month', 'news.month',
                '',
                true, '/' . PHP_EOL . 'news%'
            ])
            ->execute();
    }

    public function hookBlog($ci)
    {
        $this->ci = $ci;
        $ci->config()
            ->set('settings.path_index', 'news')
            ->set('settings.path_no_found', '')
            ->set('settings.meta_title', 'Soosyze blog')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('block', [ 'section', 'title', 'weight', 'content' ])
            ->values([
                'footer_second', 'Lorem ipsum dolor', 1,
                (new Template('block-text.php', $this->pathContent))->render()
            ])
            ->execute();

        $ci->query()
            ->insertInto('block', [
                'section', 'title',
                'weight',
                'hook', 'key_block',
                'options',
                'visibility_pages', 'pages'
            ])
            ->values([
                'content_footer', '',
                2,
                'node.next_previous', 'node.next_previous',
                json_encode([
                    'display'       => 'meta-title',
                    'next_text'     => 'Next :node_type_name',
                    'previous_text' => 'Previous :node_type_name',
                    'type'          => 'article'
                ]),
                true, 'news/%/%/%/%'
            ])
            ->values([
                'sidebar', t('Archives by months'),
                1,
                'news.month', 'news.month',
                '[]',
                false, 'admin/%' . PHP_EOL . 'user/%'
            ])
            ->values([
                'footer_first', t('Follow us'),
                1,
                'social', 'social',
                '[]',
                false, 'admin/%' . PHP_EOL . 'user/%'
            ])
            ->execute();

        $ci->query()
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('page-about.php', $this->pathContent))->render() ])
            ->execute();

        $time = (string) time();

        $idNodeAbout = $this->lastInsertId(
            'node',
            [ 'entity_id', 'type', 'date_created', 'date_changed', 'node_status_id', 'title' ],
            [ 1, 'page', $time, $time, 1, t('About') ]
        );

        $ci->query()
            ->insertInto('system_alias_url', [ 'source', 'alias' ])
            ->values([ 'node/' . $idNodeAbout, 'page/about' ])
            ->execute();

        $idMenuAbout = $this->lastInsertId(
            'menu_link',
            self::$columnsMenu,
            [ 'node.show', '', 'About', 'page/about', 'node/' . $idNodeAbout, 'menu-main', 3, -1 ]
        );

        $ci->query()->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
            ->values([ $idNodeAbout, $idMenuAbout ])
            ->execute();
    }

    public function hookPortfolio($ci)
    {
        $this->ci = $ci;
        $ci->config()
            ->set('settings.path_index', 'node/1')
            ->set('settings.path_no_found', '')
            ->set('settings.meta_title', 'Soosyze portfolio')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('page-about.php', $this->pathContent))->render() ])
            ->values([ (new Template('page-education.php', $this->pathContent))->render() ])
            ->values([ (new Template('page-project.php', $this->pathContent))->render() ])
            ->values([ (new Template('page-about.php', $this->pathContent))->render() ])
            ->values([ (new Template('page-about.php', $this->pathContent))->render() ])
            ->values([ (new Template('page-about.php', $this->pathContent))->render() ])
            ->values([ t('Not Found') ])
            ->execute();

        $time = (string) time();
        
        $columns = [
                'entity_id', 'type', 'date_created', 'date_changed', 'node_status_id',
                'title'
            ];

        $idNodeSite      = $this->lastInsertId('node', $columns, [
            1, 'page', $time, $time, 1, t('Site')
        ]);
        $idNodeEducation = $this->lastInsertId('node', $columns, [
            2, 'page', $time, $time, 1, t('Education')
        ]);
        $idNodeProjects  = $this->lastInsertId('node', $columns, [
            3, 'page', $time, $time, 1, t('Projects')
        ]);
        $idNodeProject1  = $this->lastInsertId('node', $columns, [
            4, 'page', $time, $time, 1, t('Project 1')
        ]);
        $idNodeProject2  = $this->lastInsertId('node', $columns, [
            5, 'page', $time, $time, 1, t('Project 2')
        ]);
        $idNodeProject3  = $this->lastInsertId('node', $columns, [
            6, 'page', $time, $time, 1, t('Project 3')
        ]);
        $idNodeProject4  = $this->lastInsertId('node', $columns, [
            6, 'page', $time, $time, 1, t('Project 4')
        ]);

        $ci->query()
            ->insertInto('system_alias_url', [ 'source', 'alias' ])
            ->values([ 'node/' . $idNodeSite, 'page/site' ])
            ->values([ 'node/' . $idNodeEducation, 'education' ])
            ->values([ 'node/' . $idNodeProjects, 'project' ])
            ->values([ 'node/' . $idNodeProject1, 'project/1' ])
            ->values([ 'node/' . $idNodeProject2, 'project/2' ])
            ->values([ 'node/' . $idNodeProject3, 'project/3' ])
            ->values([ 'node/' . $idNodeProject4, 'project/4' ])
            ->execute();

        $idMenuEducation = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'Education', 'education', 'node/' . $idNodeEducation, 'menu-main', 3, -1
        ]);
        $idMenuProjects  = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'Projects', 'project', 'node/' . $idNodeProjects, 'menu-main', 3, -1
        ]);
        $idMenuProject1  = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'Project 1', 'project/1', 'node/' . $idNodeProject1, 'menu-main', 4, $idMenuProjects
        ]);
        $idMenuProject2  = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'Project 2', 'project/2', 'node/' . $idNodeProject2, 'menu-main', 5, $idMenuProjects
        ]);
        $idMenuProject3  = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'Project 3', 'project/3', 'node/' . $idNodeProject3, 'menu-main', 6, $idMenuProjects
        ]);
        $idMenuProject4  = $this->lastInsertId('menu_link', self::$columnsMenu, [
            'node.show', '', 'Project 4', 'project/4', 'node/' . $idNodeProject4, 'menu-main', 7, $idMenuProjects
        ]);

        $ci->query()->update('menu_link', [ 'has_children' => true ])->where('id', 7)->execute();

        $ci->query()->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
            ->values([ $idNodeEducation, $idMenuEducation ])
            ->values([ $idNodeProjects, $idMenuProjects ])
            ->values([ $idNodeProject1, $idMenuProject1 ])
            ->values([ $idNodeProject2, $idMenuProject2 ])
            ->values([ $idNodeProject3, $idMenuProject3 ])
            ->values([ $idNodeProject4, $idMenuProject4 ])
            ->execute();

        $ci->query()
            ->insertInto('block', [ 'section', 'title', 'weight', 'content' ])
            ->values([
                'sidebar', '', 1,
                (new Template('block-about.php', $this->pathContent))->render()
            ])
            ->execute();

        $ci->query()
            ->insertInto('block', [
                'section', 'title', 'weight', 'content', 'hook', 'key_block'
            ])
            ->values([
                'sidebar', '', 1, '', 'social', 'social'
            ])
            ->execute();
    }

    public function hookOnePage($ci)
    {
        $this->ci = $ci;
        $ci->config()
            ->set('settings.path_index', 'node/1')
            ->set('settings.path_no_found', '')
            ->set('settings.meta_title', 'Soosyze One page')
            ->set('settings.logo', 'https://picsum.photos/id/30/200/200');

        $ci->query()
            ->insertInto('entity_page', [ 'body' ])
            ->values([ (new Template('block-features.php', $this->pathContent))->render() ])
            ->execute();

        $time = (string) time();

        $columns = [
                'entity_id', 'type', 'date_created', 'date_changed', 'node_status_id',
                'title'
            ];

        $idNodeSite = $this->lastInsertId('node', $columns, [
            1, 'page', $time, $time, 1, 'Ipsum sed adipiscing'
        ]);

        $ci->query()
            ->insertInto('system_alias_url', [ 'source', 'alias' ])
            ->values([ 'node/' . $idNodeSite, 'index' ])
            ->execute();

        $ci->query()
            ->insertInto('menu_link', [
                'key', 'menu', 'link', 'link_router', 'weight', 'parent', 'title_link', 'fragment'
            ])
            ->values([ 'node.show', 'menu-main', '/', 'node/' . $idNodeSite, 2, -1, 'Introduction', 'text' ])
            ->values([ 'node.show', 'menu-main', '/', 'node/' . $idNodeSite, 3, -1, 'Features', 'features' ])
            ->values([ 'node.show', 'menu-main', '/', 'node/' . $idNodeSite, 5, -1, 'About', 'img' ])
            ->values([ 'node.show', 'menu-main', '/', 'node/' . $idNodeSite, 6, -1, 'Social media', 'social' ])
            ->execute();

        $ci->query()
            ->insertInto('block', [
                'section', 'title', 'weight', 'visibility_pages', 'pages', 'content'
            ])
            ->values([
                'header', '', 1, true, '/',
                (new Template('block-learn_more.php', $this->pathContent))->render()
            ])
            ->values([
                'content_header', '<span id="text">' . t('Introduction') . '</span>',
                1, true,
                '/',
                (new Template('block-text.php', $this->pathContent))->render()
            ])
            ->values([
                'content_footer', '<span id="img">' . t('About') . '</span>', 1,
                true, '/',
                (new Template('block-img.php', $this->pathContent))->render()
            ])
            ->values([
                'footer_first', 'Lorem ipsum', 1, false, 'admin/%' . PHP_EOL . 'user/%',
                (new Template('block-text.php', $this->pathContent))->render()
            ])
            ->execute();

        $ci->query()
            ->insertInto('block', [
                'section', 'title', 'weight', 'content', 'hook', 'key_block'
            ])
            ->values([
                'footer_second',
                '<span id="social">' . t('Follow us') . '</span>',
                1,
                '',
                'social',
                'social'
            ])
            ->execute();
    }

    protected function lastInsertId($table, array $columns, array $values)
    {
        $this->ci->query()
            ->insertInto($table, $columns)
            ->values($values)
            ->execute();

        return $this->ci->schema()->getIncrement($table);
    }
}
