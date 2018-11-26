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

    public function hooks404($request, &$reponse)
    {
        if (($path = $this->config->get('settings.path_no_found')) != '') {
            $request = $request->withUri(
                $request->getUri()->withQuery($path)
            );
            $route   = $this->route->parse($request);
        }

        /*
         * Si il n'y a aucune route, une réponse sera construite à partir d'une template,
         * sinon l'execution de la route sera la page 404.
         */
        $reponse = $route
            ? $this->tpl
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Page Not Found'
                ])
                ->render('page.content', 'page-404.php', VIEWS_SYSTEM, [
                    'uri' => $request->getUri()
                ])
            : $this->route->execute($route, $request);

        if (!$reponse instanceof \Soosyze\Components\Http\Redirect) {
            $reponse = $reponse->withStatus(404);
        }
    }

    public function hooks403($request, &$reponse)
    {
        if (($path = $this->config->get('settings.path_access_denied')) != '') {
            $request = $request->withUri(
                $request->getUri()->withQuery($path)
            );
            $route   = $this->route->parse($request);
        }

        $reponse = $route
            ? $this->tpl
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Page Forbidden'
                ])
                ->render('page.content', 'page-403.php', VIEWS_SYSTEM, [
                    'uri' => $request->getUri()
                ])
            : $this->route->execute($route, $request);

        if (!$reponse instanceof \Soosyze\Components\Http\Redirect) {
            $reponse = $reponse->withStatus(403);
        }
    }

    public function hookMeta($request, &$reponse)
    {
        if ($reponse instanceof \Template\TemplatingHtml) {
            $meta = $this->config->get('settings');

            $reponse->add([
                'title'       => $meta[ 'title' ],
                'description' => $meta[ 'description' ],
                'keyboard'    => $meta[ 'keyboard' ],
                'favicon'     => $meta[ 'favicon' ]
            ]);
        }
    }
}
