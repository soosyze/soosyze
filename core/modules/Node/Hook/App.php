<?php

namespace SoosyzeCore\Node\Hook;

class App
{
    /**
     * @var \Soosyze\App
     */
    private $core;

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
            function sortEntity(evt, target) {
                let weight = 1;

                $(evt.from).children(".sort_weight").each(function () {
                    $(this).children(\'input[name*="weight"]\').val(weight);
                    weight++;
                });
            }
            </script>';

        $response->view('this', [
            'scripts' => $script
        ]);
    }
}
