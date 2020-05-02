<?php

namespace SoosyzeCore\System\Services;

use Soosyze\Components\Http\Redirect;

class HookApp
{
    protected $router;

    protected $config;

    protected $tpl;

    protected $core;

    public function __construct(
        $alias,
        $route,
        $config,
        $template,
        $core,
        $query
    ) {
        $this->alias  = $alias;
        $this->router = $route;
        $this->config = $config;
        $this->tpl    = $template;
        $this->core   = $core;
        $this->query  = $query;
        $this->views  = dirname(__DIR__) . '/Views/';
    }

    public function hookSys(&$request, &$response)
    {
        $path = $this->router->parseQueryFromRequest();

        if ($path === '/') {
            $path = $this->config->get('settings.path_index')
                ? $this->config->get('settings.path_index')
                : '404';
        }
        $path = $this->alias->getSource($path, $path);

        $request = $request
            ->withUri(
                $this->router->isRewrite()
                ? $request->getUri()->withPath($path)
                : $request->getUri()->withQuery('q=' . $path)
            );

        if (
            $this->config->get('settings.maintenance') &&
            'user/login' !== $path &&
            !$this->core->callHook('app.granted', [ 'system.config.maintenance' ])) {
            $response = $response->withStatus(503);
        }
    }

    public function hooks404($request, &$response)
    {
        if (($path = $this->config->get('settings.path_no_found', '')) !== '') {
            $path = $this->alias->getSource($path, $path);

            $requestNoFound = $request
                ->withUri(
                    $this->router->isRewrite()
                    ? $request->getUri()->withPath($path)
                    : $request->getUri()->withQuery('q=' . $path)
                )
                ->withMethod('GET');

            if ($route = $this->router->parse($requestNoFound)) {
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

        if (!$response instanceof Redirect) {
            $response = $response->withStatus(404);
        }
    }

    public function hooks403($request, &$response)
    {
        if (($path = $this->config->get('settings.path_access_denied', '')) !== '') {
            $path = $this->alias->getSource($path, $path);

            $requestDenied = $request
                ->withUri(
                    $this->router->isRewrite()
                    ? $request->getUri()->withPath($path)
                    : $request->getUri()->withQuery('q=' . $path)
                )
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

        if (!$response instanceof Redirect) {
            $response = $response->withStatus(403);
        }
    }

    public function hooks503($request, &$response)
    {
        if (($path = $this->config->get('settings.path_maintenance', '')) !== '') {
            $path = $this->alias->getSource($path, $path);

            $requestMaintenance = $request
                ->withUri(
                    $this->router->isRewrite()
                    ? $request->getUri()->withPath($path)
                    : $request->getUri()->withQuery('q=' . $path)
                )
                ->withMethod('GET');

            if ($route = $this->router->parse($requestMaintenance)) {
                $responseMaintenance = $this->router->execute($route, $requestMaintenance);
            }
        }

        if (empty($responseMaintenance) || in_array($responseMaintenance->getStatusCode(), [
                403, 404 ])) {
            $response = $this->tpl
                ->getTheme()
                ->make('page', 'page-maintenance.php', $this->views, [
                    'icon'       => '<i class="fa fa-cog" aria-hidden="true"></i>',
                    'title_main' => t('Site under maintenance'),
                ]);
        } else {
            $content = $responseMaintenance->getBlock('page.content');
            $response = $this->tpl
                ->getTheme()
                ->make('page', 'page-maintenance.php', $this->views, [
                    'icon'       => '<i class="fa fa-cog" aria-hidden="true"></i>',
                    'title_main' => t('Site under maintenance'),
                ])
                ->addBlock('page.content', $content);
        }

        if (!$response instanceof Redirect) {
            $response = $response->withStatus(503);
        }
    }

    public function hookResponseAfter($request, &$response)
    {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $meta_title       = $this->config->get('settings.meta_title', 'Soosyze CMS');
        $meta_description = $this->config->get('settings.meta_description', '');
        $meta_keyboard    = $this->config->get('settings.meta_keyboard', '');
        $favicon          = $this->config->get('settings.favicon', '');
        $logo             = $this->config->get('settings.logo', '');
        $maintenance      = $this->config->get('settings.maintenance', false);

        $vendor = $this->core->getPath('modules', 'core/modules', false) . '/System/Assets/js/script.js';

        $html      = $response->getBlock('this');
        $scripts   = $html->getVar('scripts');
        $siteDesc  = $html->getVar('description');
        $siteTitle = $html->getVar('title');
        $pageTitle = $response->getBlock('page')->getVar('title_main');

        $title = $meta_title;
        if ($siteTitle) {
            $title = str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $meta_description,
                $meta_title, $pageTitle ],
                $siteTitle
            );
        } elseif ($pageTitle) {
            $title = "$pageTitle | $title";
        }

        $description = $siteDesc
            ? str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $meta_description, $meta_title, $pageTitle ],
                $siteDesc
            )
            : $meta_description;

        $response->view('this', [
            'title'       => $title,
            'description' => $description,
            'keyboard'    => $meta_keyboard,
            'generator'   => 'Soosyze CMS',
            'favicon'     => $favicon,
            'scripts'     => $scripts . '<script src="' . $vendor . '"></script>'
        ])->view('page', [
            'title' => $meta_title,
            'logo'  => is_file(ROOT . $logo)
                ? $request->getBasePath() . $logo
                : $logo
        ]);

        $granted = $this->core->callHook('app.granted', [ 'system.config.maintenance' ]);
        if ($maintenance && $granted) {
            $response->view('page.messages', [ 'infos' => [ t('Site under maintenance') ] ]);
        }
        if ($this->router->parseQueryFromRequest() === '/' &&
            (!$maintenance ||
            ($maintenance && $granted))) {
            $response->override('page', [ 'page-front.php' ]);
        }
    }
}
