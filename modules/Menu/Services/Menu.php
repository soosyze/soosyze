<?php

namespace Menu\Services;

use Soosyze\Components\Validator\Validator;

class Menu
{
    protected $core;

    protected $config;

    protected $query;

    protected $template;

    public function __construct($core, $config, $query, $template)
    {
        $this->core     = $core;
        $this->config   = $config;
        $this->query    = $query;
        $this->template = $template;
    }

    public function find($id)
    {
        return $this->query
                ->from('menu_link')
                ->where('id', '==', $id)
                ->fetch();
    }

    public function getMenu($name)
    {
        return $this->query->from('menu')->where('name', $name);
    }

    public function getLinkPerMenu($name)
    {
        return $this->getMenu($name)
                ->leftJoin('menu_link', 'name', 'menu_link.menu')
                ->isNotNull('id');
    }

    public function isUrlOrRoute($link, $request)
    {
        $output = (new Validator())
            ->setRules([ 'link' => 'required|url' ])
            ->setInputs([ 'link' => $link ])
            ->isValid();

        if (!$output) {
            $query = $link === '/'
                ? $this->config->get('settings.path_index', '/')
                : $link;

            $uri    = $request->getUri()->withQuery($query);
            $output = $this->core->get('router')->parse($request->withUri($uri));
        }

        return $output;
    }

    public function hookMenu($request, &$reponse)
    {
        if ($reponse instanceof \Template\TemplatingHtml) {
            $this->query
                ->from('menu_link')
                ->where('active', '==', 1)
                ->orderBy('weight');
            !$reponse->isThemeAdmin()
                    ? $this->query->where('menu', 'main-menu')
                    : $this->query->where('menu', 'admin-menu');

            $query        = $this->query->fetchAll();
            $query_second = $this->query
                ->from('menu_link')
                ->where('active', '==', 1)
                ->where('menu', 'user-menu')
                ->orderBy('weight')
                ->fetchAll();

            $query_menu        = $this->getGrantedLink($query, $request);
            $query_menu_second = $this->getGrantedLink($query_second, $request);

            $reponse->render('page.main_menu', 'menu.php', VIEWS_MENU, [
                    'menu' => $query_menu
                ])
                ->render('page.second_menu', 'menu-second.php', VIEWS_MENU, [
                    'menu' => $query_menu_second
            ]);
        }
    }

    /**
     * Retire les liens restreins dans un menu et définit le lien courant.
     *
     * @param array   $query   liens du menu
     * @param Request $request
     *
     * @return array
     */
    protected function getGrantedLink($query, $request)
    {
        $route = '' !== $request->getUri()->getQuery()
            ? $request->getUri()->getQuery()
            : '/';

        foreach ($query as $key => $menu) {
            $query[ $key ][ 'link_active' ] = 0 === strpos($route, $menu[ 'link' ])
                ? 'active'
                : '';

            if (filter_var($menu[ 'link' ], FILTER_VALIDATE_URL)) {
                continue;
            }
            $menu_request = $request->withUri($request->getUri()->withQuery($menu[ 'link' ]), true);
            $menu_link    = $this->core->get('router')->parse($menu_request);

            /* Test avec un hook si le menu doit-être affiché à partir du lien du menu. */
            if (!$this->core->callHook('app.granted', [ $menu_link[ 'key' ] ])) {
                unset($query[ $key ]);

                continue;
            }
            $query[ $key ][ 'link' ] = $menu_request->getUri()->__toString();
        }

        return $query;
    }
}
