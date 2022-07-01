<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\FileManager\Hook;

use Soosyze\Components\Router\Router;
use Soosyze\Core\Modules\User\Services\User;

class Menu
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var User
     */
    private $user;

    public function __construct(Router $router, User $user)
    {
        $this->router = $router;
        $this->user   = $user;
    }

    public function hookUsersMenu(array &$menu, int $userId): void
    {
        if (!$this->user->isConnected()) {
            return;
        }

        $menu[] = [
            'key'        => 'filemanager.admin',
            'request'    => $this->router->generateRequest('filemanager.admin'),
            'title_link' => t('File')
        ];
    }

    public function hookUserManagerSubmenu(array &$menu): void
    {
        $menu[] = [
            'key'        => 'filemanager.permission.admin',
            'request'    => $this->router->generateRequest('filemanager.permission.admin'),
            'title_link' => t('Files permissions')
        ];
    }
}
