<?php

namespace SoosyzeCore\Menu\Services;

class HookApp
{
    protected $core;

    protected $query;

    public function __construct($core, $query)
    {
        $this->core  = $core;
        $this->query = $query;
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function hookResponseAfter($request, &$response)
    {
        if ($response instanceof \SoosyzeCore\Template\Services\TemplatingHtml) {
            $this->query
                ->from('menu_link')
                ->where('active', '==', 1)
                ->orderBy('weight');
            $response->isTheme('theme')
                    ? $this->query->where('menu', 'main-menu')
                    : $this->query->where('menu', 'admin-menu');

            $query        = $this->query->fetchAll();
            $query_second = $this->query->from('menu_link')
                ->where('active', '==', 1)
                ->where('menu', 'user-menu')
                ->orderBy('weight')
                ->fetchAll();

            $query_menu        = $this->getGrantedLink($query, $request);
            $query_menu_second = $this->getGrantedLink($query_second, $request);

            $response->render('page.main_menu', 'menu.php', $this->pathViews, [
                    'menu' => $query_menu
                ])
                ->render('page.second_menu', 'menu.php', $this->pathViews, [
                    'menu' => $query_menu_second
                ])->override('page.second_menu', [ 'menu-second.php' ]);
        }
    }

    public function hookMenuShowResponseAfter($request, &$response)
    {
        $script = $response->getVar('scripts');
        $style  = $response->getVar('styles');

        $script .= '<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
            <script>      
            $(document).ready(function () {
            $("#sortable").sortable({
                axis: "y",
                containment: \'table\',
                stop: function (e, ui) {
                    var i = 1;
                    $(".draggable input[type=\'number\']").each(function () {
                        this.value = i;
                        i++;
                    });
                }
            }).disableSelection();
        });</script>';

        $style .= '<style>.draggable{cursor: ns-resize;}.ui-sortable-helper{display: table;}</style>';
        $response->add([
            'scripts' => $script,
            'styles'  => $style
        ]);
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

        foreach ($query as $key => &$menu) {
            if (!$menu[ 'key' ]) {
                $menu[ 'link_active' ] = '';

                continue;
            }
            $menu[ 'link_active' ] = 0 === strpos($route, $menu[ 'link' ])
                ? 'active'
                : '';

            $link = $request->withUri($request->getUri()->withQuery($menu[ 'link' ]));
            /* Test avec un hook si le menu doit-être affiché à partir du lien du menu. */
            if (!$this->core->callHook('app.granted.route', [ $link ])) {
                unset($query[ $key ]);

                continue;
            }
            $menu[ 'link' ] = $link->getUri()->__toString();
        }

        return $query;
    }
}
