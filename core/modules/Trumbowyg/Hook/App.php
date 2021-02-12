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
            ->addScripts([
                'trumbowy.editor'        => [
                    'src' => "$assets/js/trumbowyg.js"
                ],
                'trumbowyg'              => [
                    'src' => "$vendor/trumbowyg.min.js"
                ],
                'trumbowyg.upload'       => [
                    'src' => "$vendor/plugins/upload/trumbowyg.upload.min.js"
                ],
                'trumbowyg.noembed'      => [
                    'src' => "$vendor/plugins/noembed/trumbowyg.noembed.min.js"
                ],
                'trumbowyg.preformatted' => [
                    'src' => "$vendor/plugins/preformatted/trumbowyg.preformatted.min.js"
                ],
                'trumbowyg.emoji'        => [
                    'src' => "$vendor/plugins/emoji/trumbowyg.emoji.min.js"
                ]
            ])
            ->addStyles([
                'trumbowy.editor' => [
                    'src' => "$vendor/css/trumbowyg.css"
                ],
                'trumbowyg'       => [
                    'href' => "$vendor/ui/trumbowyg.min.css",
                    'rel'  => 'stylesheet'
                ],
                'trumbowyg.emoji' => [
                    'href' => "$vendor/plugins/emoji/ui/trumbowyg.emoji.min.css",
                    'rel'  => 'stylesheet'
                ]
        ]);

        if ($lang !== 'en') {
            $response->addScript('trumbowyg.lang', [
                'src'  => "$vendor/langs/$lang.min.js",
                'type' => 'text/javascript'
            ]);
        }
    }
}
