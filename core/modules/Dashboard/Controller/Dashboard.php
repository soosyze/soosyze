<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Dashboard\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method \Soosyze\Core\Modules\System\Services\Composer     composer()
 * @method \Soosyze\Core\Modules\Dashboard\Services\Dashboard dashboard()
 * @method \Soosyze\Core\Modules\Template\Services\Templating template()
 */
class Dashboard extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function index(): ResponseInterface
    {
        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fas fa-tachometer-alt" aria-hidden="true"></i>',
                    'title_main' => t('Dashboard')
                ])
                ->make('page.content', 'dashboard/content-dashboard-dashboard.php', $this->pathViews, [
                    'link_info'    => self::router()->generateUrl('dashboard.info'),
                    'size_backup'  => self::dashboard()->getSizeBackups(),
                    'size_data'    => self::dashboard()->getSizeDatabase(),
                    'size_file'    => self::dashboard()->getSizeFiles(),
                    'version_core' => self::composer()->getVersionCore()
        ]);
    }

    public function info(ServerRequestInterface $req): ResponseInterface
    {
        ob_start();
        phpinfo();
        $phpInfo = ob_get_contents();
        ob_end_clean();

        if ($phpInfo === false) {
            $info = t('No content');
        } else {
            $info = str_replace(
                'module_Zend Optimizer',
                'module_Zend_Optimizer',
                preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpInfo) ?? ''
            );
        }

        return self::template()
                ->getTheme('theme_admin')
                ->make('page.content', 'dashboard/content-dashboard-info.php', $this->pathViews, [
                    'info' => $info
        ]);
    }
}
