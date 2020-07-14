<?php

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

class HookMenu
{
    /**
     * @var \Soosyze\Router
     */
    protected $router;

    protected $profil;

    public function __construct($router, $profil)
    {
        $this->router = $router;
        $this->profil = $profil;
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
            'link'       => $this->router->getRoute('filemanager.admin', [
                ':path' => $path
            ]),
            'title_link' => t('File')
        ];
    }

    public function hookUserManagerMenu(&$menu)
    {
        $menu[] = [
            'link'       => $this->router->getRoute('filemanager.profil.admin'),
            'title_link' => t('Files permissions'),
            'granted'    => 'filemanager.profil.admin'
        ];
    }
}
