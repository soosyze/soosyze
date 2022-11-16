<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Services;

use Core;
use Soosyze\Components\Router\Router;
use Soosyze\Core\Modules\Template\Services\Block as ServiceBlock;
use Soosyze\Core\Modules\Template\Services\Templating;

/**
 * @phpstan-import-type BlockHook from \Soosyze\Core\Modules\Block\Hook\Block
 */
class Block
{
    /**
     * @var array
     */
    private $blocks = [];

    /**
     * @var Core
     */
    private $core;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Templating
     */
    private $template;

    /**
     * @var string
     */
    private $pathViews;

    public function __construct(Core $core, Router $router, Templating $template)
    {
        $this->core     = $core;
        $this->router   = $router;
        $this->template = $template;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    /**
     * @phpstan-return BlockHook[]
     */
    public function getBlocks(): array
    {
        if (empty($this->blocks)) {
            $this->core->callHook('block.create.form.data', [ &$this->blocks ]);

            uasort($this->blocks, static function (array $a, array $b): int {
                return strcmp($a[ 'title' ], $b[ 'title' ]);
            });
        }

        return $this->blocks;
    }

    /**
     * @phpstan-return ?BlockHook
     */
    public function getBlock(string $key): ?array
    {
        return $this->getBlocks()[ $key ]
            ?? $this->getBlocks()[ str_replace('-', '.', $key) ]
            ?? null;
    }

    public function decodeOptions(?string $options): array
    {
        return is_string($options)
            ? (array) json_decode($options, true)
            : [];
    }

    public function getBlockSubmenu(string $keyRoute, string $theme, int $id): ServiceBlock
    {
        $menu = [
            [
                'class'      => 'mod',
                'icon'       => 'fa fa-edit',
                'key'        => 'block.edit',
                'link'       => $this->router->generateUrl('block.edit', [
                    'theme' => $theme,
                    'id'    => $id
                ]),
                'title_link' => t('Edit')
            ], [
                'class'      => 'mod',
                'icon'       => 'fa fa-times',
                'key'        => 'block.remove',
                'link'       => $this->router->generateUrl('block.remove', [
                    'theme' => $theme,
                    'id'    => $id
                ]),
                'title_link' => t('Delete')
            ]
        ];

        $this->core->callHook('block.submenu', [ &$menu ]);

        return $this->template
                ->getTheme('theme_admin')
                ->createBlock('block/modal-submenu.php', $this->pathViews)
                ->addVars([
                    'key_route' => $keyRoute,
                    'menu'      => $menu
        ]);
    }

    public function getBlockFieldsetSubmenu(): ServiceBlock
    {
        $menu = [
            [
                'class'      => 'active',
                'link'       => '#block-fieldset',
                'title_link' => t('Content')
            ], [
                'class'      => '',
                'link'       => '#page-fieldset',
                'title_link' => t('Visibility by pages')
            ], [
                'class'      => '',
                'link'       => '#roles-fieldset',
                'title_link' => t('Visibility by roles')
            ], [
                'class'      => '',
                'link'       => '#advanced-fieldset',
                'title_link' => t('Advanced')
            ]
        ];

        $this->core->callHook('block.fieldset.submenu', [ &$menu ]);

        return $this->template
                ->createBlock('block/submenu-block_fieldset.php', $this->pathViews)
                ->addVar('menu', $menu);
    }
}
