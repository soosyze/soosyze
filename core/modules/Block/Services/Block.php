<?php

declare(strict_types=1);

namespace SoosyzeCore\Block\Services;

use Core;
use Soosyze\Components\Router\Router;
use SoosyzeCore\Template\Services\Block as ServiceBlock;
use SoosyzeCore\Template\Services\Templating;

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

    public function getBlocks(): array
    {
        if (empty($this->blocks)) {
            $this->core->callHook('block.create.form.data', [ &$this->blocks ]);

            uasort($this->blocks, static function ($a, $b) {
                return strcmp($a['title'], $b['title']);
            });
        }

        return $this->blocks;
    }

    public function getBlock(string $key, ?array $default = null): ?array
    {
        return $this->getBlocks()[ $key ] ?? $default;
    }

    public function getBlockSubmenu(string $keyRoute, string $theme, int $id): ServiceBlock
    {
        $menu = [
            [
                'class'      => 'mod',
                'icon'       => 'fa fa-edit',
                'key'        => 'block.edit',
                'link'       => $this->router->getRoute('block.edit', [
                    ':theme'   => $theme,
                    ':id'      => $id
                ]),
                'title_link' => t('Edit')
            ], [
                'class'      => 'mod',
                'icon'       => 'fa fa-times',
                'key'        => 'block.remove',
                'link'       => $this->router->getRoute('block.remove', [
                    ':theme'   => $theme,
                    ':id'      => $id
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
