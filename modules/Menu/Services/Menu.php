<?php

namespace Menu\Services;

use System\Services\Templating;

class Menu
{
    protected $query;

    protected $template;

    public function __construct($core, $query, $template)
    {
        $this->core     = $core;
        $this->query    = $query;
        $this->template = $template;
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

    public function hookMenu($request, &$reponse)
    {
        if ($reponse instanceof Templating) {
            if (!$reponse->isThemeAdmin()) {
                $query = $this->query
                    ->from('menu')
                    ->where(function ($query) {
                        $query->where('name', "main-menu")
                        ->where('active', '==', 1);
                    })->leftJoin('menu_link', 'name', 'menu_link.menu')
                    ->orderBy('weight')
                    ->fetchAll();
            } else {
                $query = $this->query
                    ->from('menu')
                    ->where(function ($query) {
                        $query->where('active', '==', 1)
                        ->where('name', "admin-menu");
                    })->leftJoin('menu_link', 'name', 'menu_link.menu')
                    ->orderBy('weight')
                    ->fetchAll();
            }

            $query_second = $this->query
                ->from('menu')
                ->where(function ($query) {
                    $query->where('active', '==', 1)
                    ->where('name', "user-menu");
                })->leftJoin('menu_link', 'name', 'menu_link.menu')
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
     * @param array $query Liens du menu.
     * @param Request $request
     *
     * @return array
     */
    protected function getGrantedLink($query, $request)
    {
        $route = $request->getUri()->getQuery();

        foreach ($query as $key => $menu) {
            $menu_request = $request->withUri($request->getUri()->withQuery($menu[ 'target_link' ]), true);
            $menu_link    = $this->core->get('router')->parse($menu_request);

            /* Test avec un hook si le menu doit-être affiché à partir du lien du menu. */
            if (!$this->core->callHook('app.granted', [ $menu_link[ 'key' ] ])) {
                unset($query[ $key ]);

                continue;
            }

            $query[ $key ][ 'link_active' ] = strpos($route, $menu[ 'target_link' ]) === 0
                ? 'active'
                : '';
        }

        return $query;
    }
}
