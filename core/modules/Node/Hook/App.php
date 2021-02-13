<?php

namespace SoosyzeCore\Node\Hook;

use SoosyzeCore\Template\Services\Templating;

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
        if (!($response instanceof Templating)) {
            return;
        }

        $vendor = $this->core->getPath('modules', 'modules/core', false);

        $response->addScript('node', "$vendor/Node/Assets/js/node.js");
    }
}
