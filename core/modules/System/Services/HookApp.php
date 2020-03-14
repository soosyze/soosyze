<?php

namespace SoosyzeCore\System\Services;

class HookApp
{
    protected $router;

    protected $config;

    protected $tpl;

    protected $core;

    public function __construct($route, $config, $template, $core, $query)
    {
        $this->router = $route;
        $this->config = $config;
        $this->tpl    = $template;
        $this->core   = $core;
        $this->query  = $query;
        $this->views  = dirname(__DIR__) . '/Views/';
    }

    public function hookSys(&$request, &$response)
    {
        $uri   = $request->getUri();
        parse_str($uri->getQuery(), $parseQuery);
        $query = isset($parseQuery[ 'q' ])
            ? $parseQuery[ 'q' ]
            : '';

        if ($query === '' || $query === '/') {
            $path_index = $this->config->get('settings.path_index')
                ? 'q=' . $this->config->get('settings.path_index')
                : '404';
            $url        = $uri->withQuery($path_index);

            $request = $request->withUri($url)->withMethod('GET');
        }
        $alias = $this->query
            ->from('system_alias_url')
            ->where('alias', '==', $query)
            ->fetch();
        if ($alias) {
            $url     = $uri->withQuery('q=' . $alias[ 'source' ]);
            $request = $request->withUri($url)->withMethod('GET');
        }
        if (
            $this->config->get('settings.maintenance') && 'user/login' !== $query && !$this->core->callHook('app.granted', [
                'system.config.maintenance' ])) {
            $response = $response->withStatus(503);
        }
    }

    public function hooks404($request, &$response)
    {
        if (($path = $this->config->get('settings.path_no_found', '')) !== '') {
            $requestNoFound = $request
                ->withUri($request->getUri()->withQuery('q=' . $path))
                ->withMethod('GET');
            if ($route          = $this->router->parse($requestNoFound)) {
                $responseNoFound = $this->router->execute($route, $requestNoFound);
            }
        }

        /*
         * Si il n'y a aucune réponse ou que la réponse est déjà une page 404,
         * une réponse sera construite à partir d'une template,
         * sinon renvoie la réponse 404 de base.
         */
        $response = empty($responseNoFound) || $responseNoFound->getStatusCode() === 404
            ? $this->tpl
                ->view('page', [
                    'title_main' => t('Not Found')
                ])
                ->make('page.content', 'page-404.php', $this->views, [
                    'uri' => $request->getUri()
                ])
            : $responseNoFound;

        if (!$response instanceof \Soosyze\Components\Http\Redirect) {
            $response = $response->withStatus(404);
        }
    }

    public function hooks403($request, &$response)
    {
        if (($path = $this->config->get('settings.path_access_denied')) != '') {
            $requestDenied = $request
                ->withUri($request->getUri()->withQuery('q=' . $path))
                ->withMethod('GET');
            if ($route         = $this->router->parse($requestDenied)) {
                $responseDenied = $this->router->execute($route, $requestDenied);
            }
        }

        $response = empty($responseDenied) || $responseDenied->getStatusCode() === 404
            ? $this->tpl
                ->view('page', [
                    'title_main' => t('Page Forbidden')
                ])
                ->make('page.content', 'page-403.php', $this->views, [
                    'uri' => $request->getUri()
                ])
            : $responseDenied;

        if (!$response instanceof \Soosyze\Components\Http\Redirect) {
            $response = $response->withStatus(403);
        }
    }

    public function hooks503($request, &$response)
    {
        $response = $this->tpl
            ->getTheme()
            ->make('page', 'page-maintenance.php', $this->views, [
                'title_main' => '<i class="fa fa-cog" aria-hidden="true"></i> ' . t('Site under maintenance')
            ])
            ->withStatus(503);
    }

    public function hookResponseAfter($request, &$response)
    {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }
        $data = $this->config->get('settings');

        $vendor = $this->core->getPath('modules', 'core/modules', false) . '/System/Assets/js/script.js';
        
        $script      = $response->getBlock('this')->getVar('scripts');
        $description = $response->getBlock('this')->getVar('description');
        $siteTitle   = $response->getBlock('this')->getVar('title');
        $pageTitle   = $response->getBlock('page')->getVar('title_main');

        if ($siteTitle) {
            $title = str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $data[ 'meta_description' ], $data[ 'meta_title' ], $pageTitle ],
                $siteTitle
            );
        } elseif ($pageTitle) {
            $title = $pageTitle . ' | ' . $data[ 'meta_title' ];
        } else {
            $title = $data[ 'meta_title' ];
        }
        if ($description) {
            $description = str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $data[ 'meta_description' ], $data[ 'meta_title' ], $pageTitle ],
                $description
            );
        }

        $response->view('this', [
            'title'       => $title,
            'description' => $description,
            'keyboard'    => $data[ 'meta_keyboard' ],
            'favicon'     => $data[ 'favicon' ],
            'scripts'     => $script . '<script src="' . $vendor . '"></script>'
        ])->view('page', [
            'title' => $data[ 'meta_title' ],
            'logo'  => is_file(ROOT . $data[ 'logo' ])
                ? $request->getBasePath() . $data[ 'logo' ]
                : $data[ 'logo' ]
        ]);

        $granted = $this->core->callHook('app.granted', [ 'system.config.maintenance' ]);
        if ($data[ 'maintenance' ] && $granted) {
            $response->view('page.messages', [ 'infos' => [ t('Site under maintenance') ] ]);
        }
        if (in_array($request->getUri()->getQuery(), [ '', '/' ]) &&
            !$data[ 'maintenance' ] ||
            ($data[ 'maintenance' ] && $granted)) {
            $response->override('page', [ 'page-front.php' ]);
        }
    }
}
