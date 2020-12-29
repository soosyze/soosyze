<?php

namespace SoosyzeCore\FileManager\Hook;

class Menu
{
    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($router)
    {
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
