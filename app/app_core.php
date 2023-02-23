<?php

use Soosyze\App;
use Soosyze\Config;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;

class Core extends App
{
    public function loadModules(): array
    {
        /** @phpstan-ignore-next-line */
        if (!$this->get(Config::class)->get('settings.time_installed')) {
            $modules[] = new Soosyze\Core\Modules\System\Controller\Install();

            return $modules;
        }

        $modules = [];

        /** @phpstan-ignore-next-line */
        $data = $this->get(Query::class)->from('module_controller')->fetchAll();
        foreach ($data as $value) {
            /** @var class-string<Soosyze\Controller> $controller */
            $controller = str_replace(
                [ 'SoosyzeCore', 'SoosyzeExtension' ],
                [ 'Soosyze\Core\Modules', 'Soosyze\App\Modules' ],
                $value[ 'controller' ]
            );

            $modules[] = new $controller();
        }

        return $modules;
    }

    public function loadServices(): array
    {
        return [
            'schema'   => [
                'class'     => 'Soosyze\Core\Modules\QueryBuilder\Services\Schema',
                'arguments' => [
                    'host' => '#database.host',
                    'name' => '#database.schema'
                ]
            ],
            'query'    => [
                'class'     => 'Soosyze\Core\Modules\QueryBuilder\Services\Query',
                'arguments' => [
                    'schema' => '@schema'
                ]
            ],
            'template' => [
                'class'     => 'Soosyze\Core\Modules\Template\Services\Templating',
            ],
            'template.hook.user' => [
                'class' => 'Soosyze\Core\Modules\Template\Hook\User',
                'hooks' => [
                    'user.permission.module' => 'hookUserPermissionModule',
                    'install.user'           => 'hookInstallUser'
                ]
            ],
            'file'     => [
                'class'     => 'Soosyze\Core\Modules\FileSystem\Services\File',
                'arguments' => [
                    'root' => ROOT
                ]
            ],
            'translate'     => [
                'class'     => 'Soosyze\Core\Modules\Translate\Services\Translation',
                'arguments' => [
                    'dir' => __DIR__ . '/lang',
                    'langDefault' => '#settings.lang'
                ]
            ],
            'date_translate' => [
                'class' => 'Soosyze\Core\Modules\Translate\Services\DateTranslation',
                'arguments' => [
                    'dir' => __DIR__ . '/lang/date',
                    'langDefault' => '#settings.lang'
                ]
            ],
            'mailer'        => [
                'class'     => 'Soosyze\Core\Modules\Mailer\Services\Mailer',
                'arguments' => [
                    '#mailer'
                ]
            ],
            'filter' => [
                'class' => 'Soosyze\Core\Modules\Filter\Services\Filter'
            ],
            'xss' => [
                'class' => 'Soosyze\Core\Modules\Filter\Services\Xss'
            ],
            'parsedown' => [
                'class' => 'Soosyze\Core\Modules\Filter\Services\Parsedown'
            ],
            'lazyloading' => [
                'class' => 'Soosyze\Core\Modules\Filter\Services\LazyLoding'
            ]
        ];
    }
}
