<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\System\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Http\Redirect;
use Soosyze\Core\Modules\System\ExtendModule;

/**
 * @method \Soosyze\Core\Modules\System\Services\Composer     composer()
 * @method \Soosyze\Core\Modules\System\Services\Modules      module()
 * @method \Soosyze\Core\Modules\Template\Services\Templating template()
 * @method \Soosyze\Core\Modules\User\Services\User           user()
 *
 * @phpstan-import-type ToolEntity from \Soosyze\Core\Modules\System\ToolInterface
 */
class Tool extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin(): ResponseInterface
    {
        /** @phpstan-var array<ToolEntity> $tools */
        $tools = [];
        $this->container->callHook('tools.admin', [ &$tools ]);

        foreach ($tools as $key => &$tool) {
            if (!self::user()->isGrantedRequest($tool[ 'link' ])) {
                unset($tools[ $key ]);

                continue;
            }
            $tool[ 'link' ] = $tool[ 'link' ]->getUri();
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-tools" aria-hidden="true"></i>',
                    'title_main' => t('Tools')
                ])
                ->make('page.content', 'system/content-tools-admin.php', $this->pathViews, [
                    'actions'           => $this->getActions(),
                    'is_granted_action' => self::user()->isGranted('system.tool.action'),
                    'tools'             => $tools
                ]);
    }

    public function cron(ServerRequestInterface $req): ResponseInterface
    {
        $this->container->callHook('app.cron', [ $req ]);

        $_SESSION[ 'messages' ][ 'success' ][] = t('The cron task has been successfully executed');

        return new Redirect(self::router()->generateUrl('system.tool.admin'), 302);
    }

    public function updateTranslations(): ResponseInterface
    {
        $extensions   = array_column(self::module()->listModuleActive(), 'title');
        $extensions[] = self::config()->get('settings.theme');
        $extensions[] = self::config()->get('settings.theme_admin');

        $composers = self::composer()->getModuleComposers() + self::composer()->getThemeComposers();

        $composersActive = [];
        foreach ($extensions as $title) {
            /** @phpstan-var class-string<ExtendModule> $extendClass */
            $extendClass = self::composer()->getExtendClass($title, $composers);
            $extend      = new $extendClass();

            $extend->boot();

            $composersActive[ $title ] = $composers[ $title ] + [
                'translations' => $extend->getTranslations()
            ];
        }

        self::module()->loadTranslations($composersActive);

        $_SESSION[ 'messages' ][ 'success' ][] = t('The translation files have been updated');

        return new Redirect(self::router()->generateUrl('system.tool.admin'), 302);
    }

    private function getActions(): array
    {
        $actions = [
            [
                'icon'       => 'fa fa-language',
                'request'    => self::router()->generateRequest('system.tool.trans'),
                'title_link' => 'Update translation'
            ],
            [
                'icon'       => 'fa fa-concierge-bell',
                'request'    => self::router()->generateRequest('system.tool.cron'),
                'title_link' => 'Execute the cron task'
            ]
        ];
        $this->container->callHook('tools.action', [ &$actions ]);

        foreach ($actions as $key => &$action) {
            if (!self::user()->isGrantedRequest($action[ 'request' ])) {
                unset($actions[ $key ]);

                continue;
            }
            $action[ 'link' ] = $action[ 'request' ]->getUri();
        }

        return $actions;
    }
}
