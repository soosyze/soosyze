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
        $menu[] = [
            'key'        => 'filemanager.admin',
            'request'    => $this->router->getRequestByRoute('filemanager.admin'),
            'title_link' => t('File')
        ];
    }

    public function hookUserManagerSubmenu(&$menu)
    {
        $menu[] = [
            'key'        => 'filemanager.profil.admin',
            'request'    => $this->router->getRequestByRoute('filemanager.profil.admin'),
            'title_link' => t('Files permissions')
        ];
    }
}
