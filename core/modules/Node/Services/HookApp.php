<?php

namespace SoosyzeCore\Node\Services;

class HookApp
{
    /**
     * @var \Soosyze\App
     */
    protected $core;

    public function __construct($core)
    {
        $this->core = $core;
    }

    public function hookResponseAfter($request, &$response)
    {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $vendor = $this->core->getPath('modules', 'modules/core', false) . '/Node/Assets/script.js';
        $script = $response->getBlock('this')->getVar('scripts');
        $script .= '<script src="' . $vendor . '"></script>';
        $response->view('this', [ 'scripts' => $script ]);
    }

    public function hookNodeEditResponseAfter($request, &$response)
    {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $script = $response->getBlock('this')->getVar('scripts');
        $script .= '<script>
            $().ready(function () {
                var nestedSortables = [].slice.call($(\'.nested-sortable\'));

                for (var i = 0; i < nestedSortables.length; i++) {
                    new Sortable(nestedSortables[i], {
                        group: "nested",
                        animation: 150,
                        fallbackOnBody: true,
                        swapThreshold: 0.1,
                        ghostClass: "placeholder",
                        dragoverBubble: true,
                        onEnd: function (evt) {
                            render(".nested-sortable");
                        }
                    });
                }

                function render(idMenu) {
                    var weight = 1;
                    var id = $(idMenu).find(\'input[name*="id"]\').val();
                    if (id === undefined) {
                        id = -1;
                    }
                    
                    $(idMenu).children(".sort_weight").each(function () {
                        $(this).children(\'input[name*="weight"]\').val(weight);
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
