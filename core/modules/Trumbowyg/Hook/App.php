<?php

namespace SoosyzeCore\Trumbowyg\Hook;

use SoosyzeCore\Template\Services\Templating;

class App
{
    /**
     * @var \Soosyze\App
     */
    private $core;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($core, $router)
    {
        $this->core   = $core;
        $this->router = $router;
    }

    public function getEditor($request, &$response)
    {
        if (!($response instanceof Templating)) {
            return;
        }

        $assets = $this->core->getPath('modules', 'core/modules', false) . '/Trumbowyg/Assets';
        $vendor = $this->core->getPath('modules', 'core/modules', false) . '/Trumbowyg/vendor/trumbowyg/dist';
        $lang   = $this->core->get('config')->get('settings.lang', 'en');

        $response
            ->addConfigJs('trumbowyg', [
                'lang'       => $lang,
                'serverPath' => $this->router->getRoute('trumbowyg.upload')
            ])
            /* Scripts */
            ->addScript('trumbowy.editor', "$assets/js/trumbowyg.js")
            ->addScript('trumbowyg', "$vendor/trumbowyg.min.js")
            ->addScript('trumbowyg.upload', "$vendor/plugins/upload/trumbowyg.upload.min.js")
            ->addScript('trumbowyg.noembed', "$vendor/plugins/noembed/trumbowyg.noembed.min.js")
            ->addScript('trumbowyg.preformatted', "$vendor/plugins/preformatted/trumbowyg.preformatted.min.js")
            ->addScript('trumbowyg.emoji', "$vendor/plugins/emoji/trumbowyg.emoji.min.js")
            /* Styles */
            ->addStyle('trumbowy.editor', "$vendor/css/trumbowyg.css")
            ->addStyle('trumbowyg', "$vendor/ui/trumbowyg.min.css")
            ->addStyle('trumbowyg.emoji', "$vendor/plugins/emoji/ui/trumbowyg.emoji.min.css");

        if ($lang !== 'en') {
            $response->addScript('trumbowyg.lang', "$vendor/langs/$lang.min.js");
        }
    }
}
