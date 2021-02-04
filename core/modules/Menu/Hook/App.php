<?php

namespace SoosyzeCore\Menu\Hook;

use SoosyzeCore\Template\Services\Templating;

class App
{
    /**
     * @var Menu
     */
    private $menu;

    public function __construct($menu)
    {
        $this->menu = $menu;
    }

    public function hookResponseAfter($request, &$response)
    {
        if (!($response instanceof Templating)) {
            return;
        }
        $nameMenu = $response->isTheme('theme')
            ? 'menu-main'
            : 'menu-admin';

        $response
            ->addBlock('page.main_menu', $this->menu->renderMenu($nameMenu))
            ->addBlock('page.second_menu', $this->menu->renderMenu('menu-user'));
    }

    public function hookMenuShowResponseAfter($request, &$response)
    {
        if (!($response instanceof Templating)) {
            return;
        }

        $script = $response->getBlock('this')->getVar('scripts');
        $script .= '<script>
            $().ready(function () {
                let nestedSortables = [].slice.call($(\'.nested-sortable\'));

                for (let i = 0; i < nestedSortables.length; i++) {
                    new Sortable(nestedSortables[i], {
                        animation: 150,
                        dragoverBubble: true,
                        fallbackOnBody: true,
                        ghostClass: "placeholder-sortable",
                        group: "nested",
                        onEnd: function (evt) {
                            render("#main_sortable");
                        },
                        swapThreshold: 0.3
                    });
                }

                function render(idMenu) {
                    let weight = 1;
                    let id = $(idMenu).parent("li").children(\'input[name^="id"]\').val();

                    if (id === undefined) {
                        id = -1;
                    }

                    $(idMenu).children("li").each(function () {
                        $(this).children(\'input[name^="weight"]\').val(weight);
                        $(this).children(\'input[name^="parent"]\').val(id);
                        render($(this).children("ol"));
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
