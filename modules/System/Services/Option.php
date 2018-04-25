<?php

namespace System\Services;

class Option
{
    protected $tpl;

    protected $query;

    protected $route;

    protected $user;

    protected $core;

    public function __construct($route, $query, $template, $user, $core)
    {
        $this->route = $route;
        $this->tpl   = $template;
        $this->query = $query;
        $this->user  = $user;
        $this->core  = $core;
    }

    public function get($name)
    {
        $config = $this->query->select([ 'value' ])
            ->from('option')
            ->where('name', $name)
            ->fetch();

        return !is_null($config)
            ? $config[ 'value' ]
            : null;
    }

    public function getOption()
    {
        $config = $this->query
            ->from('option')
            ->fetchAll();

        foreach ($config as $value) {
            $output[ $value[ 'name' ] ] = $value[ 'value' ];
        }

        return $output;
    }

    public function hookSys(&$request)
    {
        $uri = $request->getUri();

        if ($uri->getQuery() == '' || $uri->getQuery() == '/') {
            $pathIndex = $this->get('pathIndex')
                ? '?' . $this->get('pathIndex')
                : '404';
            $url       = $uri->withQuery($pathIndex);

            $request = $request->withUri($url);
        }

        if ($this->get('maintenance')) {
            if (!preg_match("/^user.*$/", $uri->getQuery()) && !$this->user->isConnected()) {
                $request = $request->withUri($uri->withQuery('maintenance'));
            }
        }
    }

    public function hooks404($request, &$reponse)
    {
        if (($path = $this->get('pathNoFound')) != '') {
            $request = $request->withUri(
                $request->getUri()->withQuery($path)
            );
            $route   = $this->route->parse($request);
        }

        /*
         * Si il n'y a aucune route, une réponse sera construite à partir d'une template,
         * sinon l'execution de la route sera la page 404.
         */
        $reponse = empty($route)
            ? $this->tpl
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Page Not Found'
                ])
                ->render('page.content', 'page-404.php', VIEWS_SYSTEM)
            : $this->route->execute($route, $request);

        if (!$reponse instanceof \Soosyze\Components\Http\Redirect) {
            $reponse = $reponse->withStatus(404);
        }
    }

    public function hooks403($request, &$reponse)
    {
        if (($path = $this->get('pathAccessDenied')) != '') {
            $request = $request->withUri(
                $request->getUri()->withQuery($path)
            );
            $route   = $this->route->parse($request);
        }

        $reponse = empty($route)
            ? $this->tpl
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Page Forbidden'
                ])
                ->render('page.content', 'page-403.php', VIEWS_SYSTEM)
            : $this->route->execute($route, $request);

        if (!$reponse instanceof \Soosyze\Components\Http\Redirect) {
            $reponse = $reponse->withStatus(403);
        }
    }

    public function hookMeta($request, &$reponse)
    {
        if ($reponse instanceof Templating) {
            $meta = $this->getOption();

            $reponse->add([
                'title'       => $meta[ 'title' ],
                'description' => $meta[ 'description' ],
                'keyboard'    => $meta[ 'keyboard' ],
                'favicon'     => $meta[ 'favicon' ]
            ]);
        }
    }
}
