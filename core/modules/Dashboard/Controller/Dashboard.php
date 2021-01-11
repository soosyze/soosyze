<?php

namespace SoosyzeCore\Dashboard\Controller;

use Soosyze\Components\Http\Redirect;

class Dashboard extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
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
                    'link_info'   => self::router()->getRoute('dashboard.info'),
                    'link_cron'   => self::router()->getRoute('dashboard.cron'),
                    'link_trans'  => self::router()->getRoute('dashboard.trans'),
                    'size_backup' => self::dashboard()->getSizeBackups(),
                    'size_data'   => self::dashboard()->getSizeDatabase(),
                    'size_file'   => self::dashboard()->getSizeFiles()
        ]);
    }

    public function info($req)
    {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();

        return self::template()
                ->getTheme('theme_admin')
                ->make('page.content', 'dashboard/content-dashboard-info.php', $this->pathViews, [
                    'info' => str_replace(
                        'module_Zend Optimizer',
                        'module_Zend_Optimizer',
                        preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info)
                    )
        ]);
    }

    public function cron($req)
    {
        $this->container->callHook('app.cron', [ $req ]);

        $_SESSION[ 'messages' ][ 'success' ] = [ t('The cron task has been successfully executed') ];

        return new Redirect(self::router()->getRoute('dashboard.index'));
    }

    public function updateTranslations($req)
    {
        $extensions   = array_column(self::module()->listModuleActive(), 'title');
        $extensions[] = self::config()->get('settings.theme');
        $extensions[] = self::config()->get('settings.theme_admin');

        $composers = self::composer()->getModuleComposers() + self::composer()->getThemeComposers();

        $composersActive = [];
        foreach ($extensions as $title) {
            $extendClass = self::composer()->getExtendClass($title, $composers);
            $extend      = new $extendClass();

            $extend->boot();

            $composersActive[ $title ] = $composers[ $title ] + [
                'dir'          => $extend->getDir(),
                'translations' => $extend->getTranslations()
            ];
        }

        self::module()->loadTranslations($composersActive);

        $_SESSION[ 'messages' ][ 'success' ] = [ t('The translation files have been updated') ];

        return new Redirect(self::router()->getRoute('dashboard.index'));
    }
}
