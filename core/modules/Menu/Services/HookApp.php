<?php

namespace SoosyzeCore\Menu\Services;

class HookApp
{
    protected $core;

    protected $query;

    public function __construct($core, $query)
    {
        $this->core      = $core;
        $this->query     = $query;
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function hookResponseAfter($request, &$response)
    {
        if ($response instanceof \SoosyzeCore\Template\Services\Templating) {
            $nameMenu = $response->isTheme('theme')
                ? 'menu-main'
                : 'menu-admin';

            $blockMain = $this->renderMenu($nameMenu, $request, $response);
            $blockUser = $this->renderMenu('menu-user', $request, $response);

            $response
                ->addBlock('page.main_menu', $blockMain)
                ->addBlock('page.second_menu', $blockUser);
        }
    }

    public function renderMenu(
    $nameMenu,
        $request,
        $response,
        $parent = -1,
        $level = 1
    ) {
        $query = $this->query
            ->from('menu_link')
            ->where('active', '==', 1)
            ->where('menu', $nameMenu)
            ->where('parent', '==', $parent)
            ->orderBy('weight')
            ->fetchAll();

        if (empty($query)) {
            return null;
        }

        foreach ($query as &$menu) {
            $menu[ 'submenu' ] = $this->renderMenu($nameMenu, $request, $response, $menu[ 'id' ], $level + 1);
        }
        $menus = $this->getGrantedLink($query, $request);

        return $response
                ->createBlock('menu.php', $this->pathViews)
                ->nameOverride($nameMenu . '.php')
                ->addVars([ 'menu' => $menus, 'level' => $level ]);
    }

    public function hookMenuShowResponseAfter($request, &$response)
    {
        if ($response instanceof \SoosyzeCore\Template\Services\Templating) {
            $script  = $response->getBlock('this')->getVar('scripts');
            $script .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.8.3/Sortable.min.js"></script>';
            $script .= '<script>
            $().ready(function () {
                var nestedSortables = [].slice.call($(\'.nested-sortable\'));

                for (var i = 0; i < nestedSortables.length; i++) {
                    new Sortable(nestedSortables[i], {
                        group: \'nested\',
                        animation: 150,
                        fallbackOnBody: true,
                        swapThreshold: 0.1,
                        ghostClass: \'placeholder\',
                        dragoverBubble: true,
                        onEnd: function (evt) {
                            render(\'#main_sortable\');
                        }
                    });
                }

                function render(idMenu) {
                    var weight = 1;
                    var id = $(idMenu).parent(\'li\').children(\'input[name^="id"]\').val();
                    if (id === undefined) {
                        id = -1;
                    }
                    $(idMenu).children(\'li\').each(function () {
                        $(this).children(\'input[name^="weight"]\').val(weight);
                        $(this).children(\'input[name^="parent"]\').val(id);
                        render($(this).children(\'ol\'));
                        weight++;
                    });
                }
            });
            </script>';

            $response->view('this', [
                'scripts' => $script
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
            : '';

        $isRewite = $this->core->get('config')->get('settings.rewrite_engine');
        foreach ($query as $key => &$menu) {
            if (!$menu[ 'key' ]) {
                $menu[ 'link_active' ] = '';

                continue;
            }
            $menu[ 'link_active' ] = 0 === strpos($route, 'q=' . $menu[ 'link' ]) || ($route === '' && $menu[ 'link' ] === '/')
                ? 'active'
                : '';
            $link                  = $request->withUri($request->getUri()->withQuery('q=' . $menu[ 'link' ]));
            /* Test avec un hook si le menu doit-être affiché à partir du lien du menu. */
            if (!$this->core->callHook('app.granted.route', [ $link ])) {
                unset($query[ $key ]);

                continue;
            }
            $menu[ 'link' ] = $this->rewiteUri($isRewite, $link);
        }

        return $query;
    }

    protected function rewiteUri($isRewite, $request)
    {
        $query = str_replace('q=/', '', $request->getUri()->getQuery());
        $uri   = $request->getUri()->withQuery($query);

        if ($isRewite) {
            $link = $request->getBasePath();

            $link .= $uri->getQuery() !== ''
                ? str_replace('q=', '', $uri->getQuery())
                : '';

            return $link . ($uri->getFragment() !== ''
                ? '#' . $uri->getFragment()
                : '');
        }

        return $uri->__toString();
    }
}
