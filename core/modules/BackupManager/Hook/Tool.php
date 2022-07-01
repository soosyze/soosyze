<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\BackupManager\Hook;

use Soosyze\Components\Router\Router;

class Tool implements \Soosyze\Core\Modules\System\ToolInterface
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function hookToolAdmin(array &$tools): void
    {
        $tools[ 'backupmanager' ] = [
            'description' => 'Backup of the CMS',
            'icon'        => [
                'name'             => 'fa fa-file-archive',
                'background-color' => '#ac1b1b',
                'color'            => '#fff'
            ],
            'link'        => $this->router->generateRequest('backupmanager.admin'),
            'title'       => 'BackupManager'
        ];
    }
}
