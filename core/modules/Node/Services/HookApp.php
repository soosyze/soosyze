<?php

namespace SoosyzeCore\Node\Services;

class HookApp
{
    public function hookResponseAfter($response, $node)
    {
        $robots = '';
        if ($node[ 'meta_noindex' ]) {
            $robots .= 'noindex,';
        }
        if ($node[ 'meta_nofollow' ]) {
            $robots .= 'nofollow,';
        }
        if ($node[ 'meta_noarchive' ]) {
            $robots .= 'noarchive,';
        }
        if ($robots) {
            $response->view('this', [
                'meta' => '<meta name="robots" content="' . substr($robots, 0, -1) . '">' . PHP_EOL
            ]);
        }
        if ($node[ 'node_status_id' ] != 1) {
            $response->view('page.messages', [
                'infos' => [ t('This content is not published') ]
            ]);
        }
    }

    public function hookMenuShowResponseAfter($request, &$response)
    {
        if ($response instanceof \SoosyzeCore\Template\Services\Templating) {
            $script  = $response->getBlock('this')->getVar('scripts');
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
}
