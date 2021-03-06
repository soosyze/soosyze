<?php

namespace SoosyzeCore\System\Hook;

use Soosyze\Components\Http\Redirect;
use SoosyzeCore\Template\Services\Templating;

class App
{
    /**
     * @var \SoosyzeCore\System\Services\Alias
     */
    private $alias;

    /**
     * @var \Soosyze\Config
     */
    private $config;

    /**
     * @var \Soosyze\App
     */
    private $core;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var \SoosyzeCore\QueryBuilder\Services\Query
     */
    private $query;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    /**
     * @var Templating
     */
    private $tpl;

    public function __construct(
        $alias,
        $config,
        $core,
        $query,
        $route,
        $template
    ) {
        $this->alias  = $alias;
        $this->config = $config;
        $this->core   = $core;
        $this->query  = $query;
        $this->router = $route;
        $this->tpl    = $template;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function hookSys(&$request, &$response)
    {
        $path = $this->router->parseQueryFromRequest();
        $path = $this->alias->getSource($path, $path);

        $request = $request
            ->withUri(
                $this->router->isRewrite()
                ? $request->getUri()->withPath($path)
                : $request->getUri()->withQuery('q=' . $path)
            );

        if (
            $this->config->get('settings.maintenance') &&
            $path !== 'user/login' &&
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
                ->make('page.content', 'page-404.php', $this->pathViews, [
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

            if ($route = $this->router->parse($requestDenied)) {
                $responseDenied = $this->router->execute($route, $requestDenied);
            }
        }

        $response = empty($responseDenied) || $responseDenied->getStatusCode() === 404
            ? $this->tpl
                ->view('page', [
                    'title_main' => t('Page Forbidden')
                ])
                ->make('page.content', 'page-403.php', $this->pathViews, [
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
                ->make('page', 'page-maintenance.php', $this->pathViews, [
                    'icon'       => '<i class="fa fa-cog" aria-hidden="true"></i>',
                    'title_main' => t('Site under maintenance'),
                ]);
        } else {
            $content = $responseMaintenance->getBlock('page.content');
            $response = $this->tpl
                ->getTheme()
                ->make('page', 'page-maintenance.php', $this->pathViews, [
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
        if (!($response instanceof Templating)) {
            return;
        }

        $metaTitle       = $this->config->get('settings.meta_title', 'Soosyze CMS');
        $metaDescription = $this->config->get('settings.meta_description', '');
        $favicon         = $this->config->get('settings.favicon', '')
            ? $this->router->getBasePath() . $this->config->get('settings.favicon')
            : '';

        $logo        = $this->config->get('settings.logo', '');
        $maintenance = $this->config->get('settings.maintenance', false);

        $vendor = $this->core->getPath('modules', 'core/modules', false) . '/System/Assets';

        $html      = $response->getBlock('this');
        $siteDesc  = $html->getVar('description');
        $siteTitle = $html->getVar('title');
        $pageTitle = $response->getBlock('page')->getVar('title_main');

        $title = $metaTitle;
        if ($siteTitle) {
            $title = str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $metaDescription, $metaTitle, $pageTitle ],
                $siteTitle
            );
        } elseif ($pageTitle) {
            $title = "$pageTitle | $title";
        }

        $description = $siteDesc
            ? str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $metaDescription, $metaTitle, $pageTitle ],
                $siteDesc
            )
            : $metaDescription;

        $response->view('this', [
                'title'       => $title,
                'favicon'     => $favicon
            ])
            ->addScript('system', "$vendor/js/system.js")
            ->addStyle('system', "$vendor/css/system.css")
            ->addMetas([
                [
                    'name'    => 'keyboard',
                    'content' => $this->config->get('settings.meta_keyboard', '')
                ], [
                    'name'    => 'description',
                    'content' => $description
                ], [
                    'name'    => 'generator',
                    'content' => 'Soosyze CMS'
                ]
            ])
            ->view('page', [
                'title' => $metaTitle,
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
            $response->getBlock('page')->setNamesOverride([ 'page-front.php' ]);
        }
    }
}
