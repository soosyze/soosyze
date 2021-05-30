<?php

namespace SoosyzeCore\Trumbowyg\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
        $permissions[ 'Trumbowyg' ] = [
            'trumbowyg.upload' => 'Use image upload'
        ];
    }

    public function hookUpload()
    {
        return 'trumbowyg.upload';
    }
}
