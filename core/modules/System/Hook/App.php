<?php

declare(strict_types=1);

namespace SoosyzeCore\System\Hook;

use Core;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Http\Uri;
use Soosyze\Components\Router\Router;
use Soosyze\Config;
use SoosyzeCore\System\Hook\Config as HookConfig;
use SoosyzeCore\System\Services\Alias;
use SoosyzeCore\Template\Services\Templating;

class App
{
    /**
     * @var Alias
     */
    private $alias;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Core
     */
    private $core;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Templating
     */
    private $tpl;

    public function __construct(
        Alias $alias,
        Config $config,
        Core $core,
        Router $router,
        Templating $template
    ) {
        $this->alias  = $alias;
        $this->config = $config;
        $this->core   = $core;
        $this->router = $router;
        $this->tpl    = $template;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function hookSys(
        RequestInterface &$request,
        ResponseInterface &$response
    ): void {
        $path = $this->router->getPathFromRequest($request);
        /** @phpstan-var string $path */
        $path = $this->alias->getSource($path, $path);

        $request = $request
            ->withUri(
                Uri::create($this->router->getBasePath() . '/' . ltrim($path, '/'))
            );

        if (
            $this->config->get('settings.maintenance', HookConfig::MAINTENANCE) &&
            $path !== 'user/login' &&
            !$this->core->callHook('app.granted', [ 'system.config.maintenance' ])) {
            $response = $response->withStatus(503);
        }
    }

    public function hooks404(
        RequestInterface $request,
        ResponseInterface &$response
    ): void {
        /** @phpstan-var string $pathNoFound */
        $pathNoFound     = $this->config->get('settings.path_no_found', '');
        $path            = '/' . ltrim($pathNoFound, '/');
        $responseNoFound = null;

        if ($path !== '') {
            /** @phpstan-var string $path */
            $path = $this->alias->getSource($path, $path);

            $requestNoFound = $request
                ->withUri(
                    Uri::create($this->router->getBasePath() . $path)
                )
                ->withMethod('GET');

            if (($route = $this->router->parse($requestNoFound)) !== null) {
                $responseNoFound = $this->router->execute($route, $requestNoFound);
            }
        }

        /*
         * Si il n'y a aucune réponse ou que la réponse est déjà une page 404,
         * une réponse sera construite à partir d'un template,
         * sinon renvoie la réponse 404 de base.
         */
        $response = !($responseNoFound instanceof ResponseInterface) || $responseNoFound->getStatusCode() === 404
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

    public function hooks403(
        RequestInterface $request,
        ResponseInterface &$response
    ): void {
        /** @phpstan-var string $pathAccessDenied */
        $pathAccessDenied = $this->config->get('settings.path_access_denied', '');
        $path             = '/' . ltrim($pathAccessDenied, '/');
        $responseDenied   = null;

        if ($path !== '') {
            /** @phpstan-var string $path */
            $path = $this->alias->getSource($path, $path);

            $requestDenied = $request
                ->withUri(
                    Uri::create($this->router->getBasePath() . $path)
                )
                ->withMethod('GET');

            if (($route = $this->router->parse($requestDenied)) !== null) {
                $responseDenied = $this->router->execute($route, $requestDenied);
            }
        }

        $response = !($responseDenied instanceof ResponseInterface) || $responseDenied->getStatusCode() === 404
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

    public function hooks503(
        RequestInterface $request,
        ResponseInterface &$response
    ): void {
        /** @phpstan-var string $pathMaintenance */
        $pathMaintenance     = $this->config->get('settings.path_maintenance', '');
        $path                = '/' . ltrim($pathMaintenance, '/');
        $responseMaintenance = null;

        if ($path !== '') {
            $path = $this->alias->getSource($path, $path);

            $requestMaintenance = $request
                ->withUri(
                    Uri::create($this->router->getBasePath() . $path)
                )
                ->withMethod('GET');

            if (($route = $this->router->parse($requestMaintenance)) !== null) {
                $responseMaintenance = $this->router->execute($route, $requestMaintenance);
            }
        }

        if (!($responseMaintenance instanceof Templating)
            || in_array($responseMaintenance->getStatusCode(), [ 403, 404 ])) {
            $response = $this->tpl
                ->getTheme()
                ->make('page', 'page-maintenance.php', $this->pathViews, [
                    'icon'       => '<i class="fa fa-cog" aria-hidden="true"></i>',
                    'title_main' => t('Site under maintenance'),
                ]);
        } else {
            $content  = $responseMaintenance->getBlock('page.content');
            $response = $this->tpl
                ->getTheme()
                ->make('page', 'page-maintenance.php', $this->pathViews, [
                    'icon'       => '<i class="fa fa-cog" aria-hidden="true"></i>',
                    'title_main' => t('Site under maintenance'),
                ])
                ->addBlock('page.content', $content);
        }

        $response = $response->withStatus(503);
    }

    public function hookResponseAfter(
        RequestInterface $request,
        ResponseInterface &$response
    ): void {
        if (!($response instanceof Templating)) {
            return;
        }

        /** @phpstan-var string $metaTitle */
        $metaTitle       = $this->config->get('settings.meta_title', HookConfig::META_TITLE);
        /** @phpstan-var string $metaDescription */
        $metaDescription = $this->config->get('settings.meta_description', HookConfig::META_DESCRIPTION);
        /** @phpstan-var string $favicon */
        $favicon         = $this->config->get('settings.favicon', '');

        $logo        = $this->config->get('settings.logo', '');
        /** @phpstan-var bool $maintenance */
        $maintenance = $this->config->get('settings.maintenance', HookConfig::MAINTENANCE);

        $vendor = $this->core->getPath('modules', 'core/modules', false) . '/System/Assets';

        $html      = $response->getBlock('this');
        /** @phpstan-var string $siteDesc */
        $siteDesc  = $html->getVar('description');
        /** @phpstan-var string $siteTitle */
        $siteTitle = $html->getVar('title');
        /** @phpstan-var string $pageTitle */
        $pageTitle = $response->getBlock('page')->getVar('title_main');

        $title = $metaTitle;
        if ($siteTitle !== '') {
            $title = str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $metaDescription, $metaTitle, $pageTitle ],
                $siteTitle
            );
        } elseif ($pageTitle !== '') {
            $title = "$pageTitle | $title";
        }

        $description = $siteDesc !== ''
            ? str_replace(
                [ ':site_description', ':site_title', ':page_title' ],
                [ $metaDescription, $metaTitle, $pageTitle ],
                $siteDesc
            )
            : $metaDescription;

        $response->view('this', [
                'favicon' => is_file(ROOT . $favicon)
                    ? $this->router->getBasePath() . '/' . $favicon
                    : $favicon,
                'title'   => $title
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
                'logo'  => is_file(ROOT . $logo)
                    ? $this->router->getBasePath() . '/' . $logo
                    : $logo,
                'title' => $metaTitle
        ]);
        /** @phpstan-var bool $granted */
        $granted = $this->core->callHook('app.granted', [ 'system.config.maintenance' ]);
        if ($maintenance && $granted) {
            $_SESSION[ 'messages' ][ 'infos' ][] = t('Site under maintenance');
        }
        /** @phpstan-ignore-next-line */
        if ($this->router->getPathFromRequest() === '/' && (!$maintenance || ($maintenance && $granted))) {
            $response->getBlock('page')->setNamesOverride([ 'page-front.php' ]);
        }
    }
}
