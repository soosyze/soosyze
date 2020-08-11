<?php

namespace SoosyzeCore\Dashboard\Controller;

use Soosyze\Components\Http\Redirect;

class Dashboard extends \Soosyze\Controller
{
    protected $pathViews;

    public function __construct()
    {
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function index()
    {
        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fas fa-tachometer-alt" aria-hidden="true"></i>',
                    'title_main' => t('Dashboard')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'dashboard/content-dashboard-dashboard.php', $this->pathViews, [
                    'link_about'  => self::router()->getRoute('dashboard.about'),
                    'link_cron'   => self::router()->getRoute('dashboard.cron'),
                    'link_trans'  => self::router()->getRoute('dashboard.trans'),
                    'size_backup' => self::dashboard()->getSizeBackups(),
                    'size_data'   => self::dashboard()->getSizeDatabase(),
                    'size_file'   => self::dashboard()->getSizeFiles()
        ]);
    }

    public function about($req)
    {
        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fas fa-tachometer-alt" aria-hidden="true"></i>',
                    'title_main' => t('About')
                ])
                ->make('page.content', 'dashboard/content-dashboard-about.php', $this->pathViews);
    }

    public function cron($req)
    {
        $this->container->callHook('app.cron', [ $req ]);

        $_SESSION[ 'messages' ][ 'success' ] = [ t('The cron task has been successfully executed') ];

        return new Redirect(self::router()->getRoute('dashboard.index'));
    }
    
    public function updateTranslations($req)
    {
        $modules  = array_keys(self::module()->listModuleActive());
        $composer = self::composer()->getAllComposer();

        $composerActive = [];
        foreach ($modules as $title) {
            $migration = self::composer()->getNamespace($title) . 'Installer';
            $installer = new $migration();
            
            $installer->boot();

            $composerActive[ $title ] = $composer[ $title ] + [
                'dir'          => $installer->getDir(),
                'translations' => $installer->getTranslations()
            ];
        }

        self::module()->loadTranslations($modules, $composerActive);
        $_SESSION[ 'messages' ][ 'success' ] = [ t('The translation files have been updated') ];

        return new Redirect(self::router()->getRoute('dashboard.index'));
    }
}
