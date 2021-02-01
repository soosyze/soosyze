<?php

namespace SoosyzeCore\BackupManager\Hook;

class Tool implements \SoosyzeCore\System\ToolInterface
{
    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function hookToolAdmin(array &$tools)
    {
        $tools[ 'backupmanager' ] = [
            'description' => 'Backup of the CMS',
            'icon'        => [
                'name'             => 'fa fa-file-archive',
                'background-color' => '#ac1b1b',
                'color'            => '#fff'
            ],
            'link'        => $this->router->getRequestByRoute('backupmanager.admin'),
            'title'       => 'BackupManager'
        ];
    }
}
