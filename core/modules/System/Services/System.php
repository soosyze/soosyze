<?php

namespace System\Services;

class System
{
    protected $tpl;

    protected $query;

    protected $route;

    protected $user;

    public function __construct($route, $config, $template, $user)
    {
        $this->route  = $route;
        $this->tpl    = $template;
        $this->config = $config;
        $this->user   = $user;
    }

    public function hookSys(&$request)
    {
        $uri = $request->getUri();

        if ($uri->getQuery() == '' || $uri->getQuery() == '/') {
            $path_index = $this->config->get('settings.path_index')
                ? '?' . $this->config->get('settings.path_index')
                : '404';
            $url        = $uri->withQuery($path_index);

            $request = $request->withUri($url);
        }

        if ($this->config->get('settings.maintenance')) {
            if (!preg_match('/^user.*$/', $uri->getQuery()) && !$this->user->isConnected()) {
                $request = $request->withUri($uri->withQuery('maintenance'));
            }
        }
    }

    public function hooks404($request, &$response)
    {
        if (($path = $this->config->get('settings.path_no_found')) != '') {
            $requestNoFound = $request->withUri(
                $request->getUri()->withQuery($path)
            );
            if (($route          = $this->route->parse($requestNoFound))) {
                $responseNoFound = $this->route->execute($route, $requestNoFound);
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
                    'title_main' => 'Page Not Found'
                ])
                ->render('page.content', 'page-404.php', VIEWS_SYSTEM, [
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
            $requestDenied = $request->withUri(
                $request->getUri()->withQuery($path)
            );
            if (($route         = $this->route->parse($requestDenied))) {
                $responseDenied = $this->route->execute($route, $requestDenied);
            }
        }

        $response = empty($responseDenied) || $responseDenied->getStatusCode() === 404
            ? $this->tpl
                ->view('page', [
                    'title_main' => 'Page Forbidden'
                ])
                ->render('page.content', 'page-403.php', VIEWS_SYSTEM, [
                    'uri' => $request->getUri()
                ])
            : $responseDenied;

        if (!$response instanceof \Soosyze\Components\Http\Redirect) {
            $response = $response->withStatus(403);
        }
    }

    public function hookMeta($request, &$response)
    {
        if ($response instanceof \Template\Services\TemplatingHtml) {
            $uri = $request->getUri();

            if ($uri->getQuery() == '' || $uri->getQuery() == '/') {
                $response->override('page', [ 'page-front.php' ]);
            }
            $meta = $this->config->get('settings');

            $response->add([
                'title'       => $meta[ 'title' ],
                'description' => $meta[ 'description' ],
                'keyboard'    => $meta[ 'keyboard' ],
                'favicon'     => $meta[ 'favicon' ]
            ])->view('page', [
                'title' => $meta[ 'title' ],
                'logo'  => $meta[ 'logo' ]
            ]);
        }
    }
}
