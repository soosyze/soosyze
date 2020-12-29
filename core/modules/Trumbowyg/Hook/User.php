<?php

namespace SoosyzeCore\Trumbowyg\Hook;

class User
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
