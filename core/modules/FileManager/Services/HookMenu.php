<?php

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

class HookMenu
{
    protected $profil;

    /**
     * @var \Soosyze\Router
     */
    protected $router;

    public function __construct($profil, $router)
    {
        $this->profil = $profil;
        $this->router = $router;
    }

    public function hookUsersMenu(&$menu, $userId)
    {
        $profils = $this->profil->getProfilsFileByUser($userId);
        if (empty($profils)) {
            return;
        }

        $path = Util::cleanPath($profils[ 0 ][ 'folder_show' ]);
        $path = str_replace(':user_id', $userId, $path);

        $menu[] = [
            'key'        => 'filemanager.admin',
            'request'    => $this->router->getRequestByRoute('filemanager.admin', [
                ':path' => $path
            ]),
            'title_link' => t('File')
        ];
    }

    public function hookUserManagerMenu(&$menu)
    {
        $menu[] = [
            'key'        => 'filemanager.profil.admin',
            'request'    => $this->router->getRequestByRoute('filemanager.profil.admin'),
            'title_link' => t('Files permissions')
        ];
    }
}
