<?php

namespace SoosyzeCore\Trumbowyg\Services;

class Trumbowyg
{
    /**
     * @var \Soosyze\App
     */
    protected $core;

    public function __construct($core, $router)
    {
        $this->core   = $core;
        $this->router = $router;
    }

    public function getEditor($request, &$response)
    {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $vendor = $this->core->getPath('modules', 'core/modules', false) . '/Trumbowyg/vendor/';
        $script = $response->getBlock('this')->getVar('scripts');
        $style  = $response->getBlock('this')->getVar('styles');
        $script .= '<script src="' . $vendor . 'dist/trumbowyg.min.js"></script>';
        if (($lang = $this->core->get('config')->get('settings.lang', 'en')) !== 'en') {
            $script .='<script type="text/javascript" src="' . $vendor . 'dist/langs/' . $lang . '.min.js"></script>';
        }
        $script .= '<script src="' . $vendor . 'dist/plugins/upload/trumbowyg.upload.min.js"></script>
                        <script src="' . $vendor . 'dist/plugins/noembed/trumbowyg.noembed.min.js"></script>
                        <script src="' . $vendor . 'dist/plugins/preformatted/trumbowyg.preformatted.min.js"></script>
                        <script src="' . $vendor . 'dist/plugins/emoji/trumbowyg.emoji.min.js"></script>
			<script>
                        $(function(){
                        addEditor();
                    }); 
                    function addEditor() {
                            $.trumbowyg.svgPath = "' . $vendor . 'dist/icons.svg";
                            $("textarea.editor").trumbowyg({
                                lang: "fr",
                                btnsDef: {
                                    // Create a new dropdown
                                    image: {
                                        dropdown: ["insertImage", "upload", "noembed"],
                                        ico: "insertImage"
                                    },
                                    customFormatting: {
                                        dropdown: ["p", "blockquote", "h2", "h3", "h4"],
                                        ico: "p"
                                    }
                                },
                                // Redefine the button pane
                                btns: [
                                    ["viewHTML"],
                                    ["undo", "redo"], // Only supported in Blink browsers
                                    ["customFormatting", "removeformat"],
                                    ["preformatted"],
                                    ["emoji"],
                                    ["strong", "em", "del"],
                                    ["superscript", "subscript"],
                                    ["link"],
                                    ["image"],
                                    ["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],
                                    ["unorderedList", "orderedList"],
                                    ["horizontalRule"],
                                    ["fullscreen"]
                                ],
                                imageWidthModalEdit: true,
                                plugins: {
                                    // Add imagur parameters to upload plugin for demo purposes
                                    upload: {
                                        serverPath: "' . $this->router->getRoute('trumbowyg.upload') . '",
                                        fileFieldName: "image",
                                        urlPropertyName: "link"
                                    }
                                },
                                semantic: {
                                    "b": "strong",
                                    "s": "del",
                                    "strike": "del",
                                    "div": "div"
                                },
                                tagsToKeep: ["hr", "img", "embed", "iframe", "i"],
                                tagsToRemove: ["applet", "embed", "form", "input", "link", "option",  "script", "select", "textarea"]
                            });
                        }
                    </script>';
        $style  .= '<link rel="stylesheet" href="' . $vendor . 'dist/ui/trumbowyg.min.css">
                    <link rel="stylesheet" href="' . $vendor . 'dist/plugins/emoji/ui/trumbowyg.emoji.min.css">
                        <style>
                        .trumbowyg-editor[contenteditable=true]:empty::before{
                            content: attr(placeholder);
                            color: #999;
                        }
                        </style>';
        $response->view('this', [
            'scripts' => $script,
            'styles'  => $style
        ]);
    }
}
