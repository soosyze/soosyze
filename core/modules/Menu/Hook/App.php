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
            function sortMenu(evt) {
                let weight = 1;
                let id = $(evt.to).parent("li").children(\'input[name^="id"]\').val();

                if (id === undefined) {
                    id = -1;
                }

                $(evt.to).children("li").each(function () {
                    $(this).children(\'input[name^="weight"]\').val(weight);
                    $(this).children(\'input[name^="parent"]\').val(id);
                    weight++;
                });
            }
            </script>';

        $response->view('this', [
            'scripts' => $script
        ]);
    }
}
