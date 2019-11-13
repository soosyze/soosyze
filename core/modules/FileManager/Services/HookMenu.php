<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

/**
 * Description of HookMenu
 *
 * @author mnoel
 */
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

    public function hookUsersMenu(&$menu, $id_user)
    {
        $profils = $this->profil->getProfilsFileByUser($id_user);
        if (empty($profils)) {
            return;
        }
        $path   = Util::cleanPath($profils[ 0 ][ 'folder_show' ]);
        $path   = str_replace('%uid', $id_user, $path);
        $menu[] = [
            'link'       => $this->router->getRoute('filemanager.admin', [
                ':path' => $path
            ]),
            'title_link' => t('Fichiers')
        ];
    }

    public function hookUsersManagementMenu(&$menu, $user)
    {
        if ($user->isGranted('filemanager.profil.admin')) {
            $menu[] = [
                'link'       => $this->router->getRoute('filemanager.profil.admin'),
                'title_link' => t('Administer file profiles')
            ];
        }
    }
}
