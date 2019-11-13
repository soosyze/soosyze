<?php

namespace SoosyzeCore\FileManager\Services;

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

    public function hookReponseAfter($request, &$response)
    {
        if ($response instanceof \SoosyzeCore\Template\Services\Templating) {
            $vendor = $this->core->getPath('modules', 'modules/core', false) . '/FileManager/Assets/script.js';
            $script = $response->getBlock('this')->getVar('scripts');
            $script .= '<script src="' . $vendor . '"></script>';

            $vendor = $this->core->getPath('modules', 'modules/core', false) . '/FileManager/Assets/style.css';
            $styles = $response->getBlock('this')->getVar('styles');
            $styles .= '<link rel="stylesheet" href="' . $vendor . '">' . PHP_EOL;

            $response->view('this', [ 'scripts' => $script, 'styles' => $styles ]);
        }
    }
}
