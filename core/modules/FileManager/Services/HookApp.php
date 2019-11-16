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
            $vendor = $this->core->getPath('modules', 'modules/core', false);
            $script = $response->getBlock('this')->getVar('scripts');
            $script .= '<script src="' . $vendor . '/FileManager/Assets/script.js"></script>';

            $styles = $response->getBlock('this')->getVar('styles');
            $styles .= '<link rel="stylesheet" href="' . $vendor . '/FileManager/Assets/style.css">' . PHP_EOL;
            $styles .= "<style>
            .file { background-image: url($vendor/FileManager/Assets/icon/file.svg); }
            .ai, .eps{ background-image: url($vendor/FileManager/Assets/icon/ai.svg); }
            .dir{ background-image: url($vendor/FileManager/Assets/icon/dir.svg); }
            .gif, .ico, .jpg, .jpeg, .png, .svg{ background-image: url($vendor/FileManager/Assets/icon/jpg.svg); }
            .json, .css{ background-image: url($vendor/FileManager/Assets/icon/json.svg); }
            .mp3{ background-image: url($vendor/FileManager/Assets/icon/mp3.svg); }
            .mp4, .avi, .mpeg, .webm{ background-image: url($vendor/FileManager/Assets/icon/mp4.svg); }
            .pdf{ background-image: url($vendor/FileManager/Assets/icon/pdf.svg); }
            .odp, .pptx, .ppt{ background-image: url($vendor/FileManager/Assets/icon/ppt.svg); }
            .txt, .odt, .docx, .doc{ background-image: url($vendor/FileManager/Assets/icon/txt.svg); }
            .xml, .xhtml, .html{ background-image: url($vendor/FileManager/Assets/icon/xhtml.svg); }
            .ods, .xlsx, .xls, csv{ background-image: url($vendor/FileManager/Assets/icon/xls.svg); }
            .zip, .gzip, .tar, .rar{ background-image: url($vendor/FileManager/Assets/icon/zip.svg); }
            </style>" . PHP_EOL;

            $response->view('this', [ 'scripts' => $script, 'styles' => $styles ]);
        }
    }
}
