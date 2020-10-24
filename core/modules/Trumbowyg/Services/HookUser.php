<?php

namespace SoosyzeCore\Trumbowyg\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Trumbowyg' ] = [
            'trumbowyg.upload' => 'Use image upload'
        ];
    }

    public function hookUpload()
    {
        return 'trumbowyg.upload';
    }
}