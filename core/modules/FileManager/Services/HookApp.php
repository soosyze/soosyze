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

    public function hookResponseAfter($request, &$response)
    {
        if ($response instanceof \SoosyzeCore\Template\Services\Templating) {
            $vendor = $this->core->getPath('modules', 'modules/core', false);
            $script = $response->getBlock('this')->getVar('scripts');
            $script .= '<script src="' . $vendor . '/FileManager/Assets/script.js"></script>';

            $styles = $response->getBlock('this')->getVar('styles');
            $styles .= '<link rel="stylesheet" href="' . $vendor . '/FileManager/Assets/style.css">' . PHP_EOL;

            $response->view('this', [ 'scripts' => $script, 'styles' => $styles ]);
        }
    }
}
