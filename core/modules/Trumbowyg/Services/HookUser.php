<?php

namespace SoosyzeCore\Trumbowyg\Services;

class HookUser
{
    public function hookPermission(&$permission)
    {
        $permission[ 'Trumbowyg' ] = [
            'trumbowyg.upload' => t('Utiliser l\'upload d\'image')
        ];
    }

    public function hookUpload()
    {
        return 'trumbowyg.upload';
    }
}
