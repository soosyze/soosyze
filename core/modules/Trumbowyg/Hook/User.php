<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Trumbowyg\Hook;

class User implements \Soosyze\Core\Modules\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
        $permissions[ 'Trumbowyg' ] = [
            'trumbowyg.upload' => 'Use image upload'
        ];
    }

    public function hookUpload(): string
    {
        return 'trumbowyg.upload';
    }
}
