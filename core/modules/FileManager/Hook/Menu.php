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

    public function hookUsersMenu(array &$menu, $userId)
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

    public function hookUserManagerSubmenu(array &$menu)
    {
        $menu[] = [
            'key'        => 'filemanager.permission.admin',
            'request'    => $this->router->getRequestByRoute('filemanager.permission.admin'),
            'title_link' => t('Files permissions')
        ];
    }
}
