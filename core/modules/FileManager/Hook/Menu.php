<?php

namespace SoosyzeCore\FileManager\Hook;

class Menu
{
    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    /**
     * @var \SoosyzeCore\User\Services\User
     */
    private $user;

    public function __construct($router, $user)
    {
        $this->router = $router;
        $this->user   = $user;
    }

    public function hookUsersMenu(&$menu, $userId)
    {
        if (!$this->user->isConnected()) {
            return;
        }

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
