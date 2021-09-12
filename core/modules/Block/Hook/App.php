<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Hook;

use Core;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soosyze\Components\Router\Router;
use Soosyze\Core\Modules\Block\Services\Block;
use Soosyze\Core\Modules\Block\Services\Style;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;
use Soosyze\Core\Modules\Template\Services\Block as ServicesBlock;
use Soosyze\Core\Modules\Template\Services\Templating;
use Soosyze\Core\Modules\User\Services\User;

/**
 * @phpstan-import-type BlockEntity from \Soosyze\Core\Modules\Block\Extend
 */
class App
{
    /**
     * @var Block
     */
    private $block;

    /**
     * @var Core
     */
    private $core;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Style
     */
    private $style;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Templating
     */
    private $tpl;

    /**
     * Données de l'utilisateur courant.
     *
     * @var array|null
     */
    private $userCurrent;

    public function __construct(
        Block $block,
        Core $core,
        Query $query,
        Router $router,
        Style $style,
        Templating $template,
        User $user
    ) {
        $this->block  = $block;
        $this->core   = $core;
        $this->query  = $query;
        $this->router = $router;
        $this->style  = $style;
        $this->tpl    = $template;
        $this->block  = $block;

        $this->pathViews = dirname(__DIR__) . '/Views/';

        $this->userCurrent = $user->isConnected();

        if ($this->userCurrent) {
            $this->roles   = $user->getRolesUser($this->userCurrent[ 'user_id' ]);
            $this->roles[] = [ 'role_id' => 2 ];
        }
    }

    public function hookResponseAfter(
        RequestInterface $request,
        ResponseInterface &$response
    ): void {
        if (!($response instanceof Templating) || !in_array($response->getStatusCode(), [200, 403, 404])) {
            return;
        }

        $theme   = $this->getNameTheme();
        $isAdmin = $this->isAdmin();

        $blocks = $this->getBlocks($theme, $isAdmin);

        $sections = $this->tpl->getSections();

        foreach ($sections as $section) {
            $response->make('page.' . $section, 'section.php', $this->pathViews, [
                'section_id'  => $section,
                'content'     => $blocks[ $section ] ?? [],
                'is_admin'    => $isAdmin,
                'link_create' => ($isAdmin && ($section !== 'main_menu' || ($section === 'main_menu' && empty($blocks[ 'main_menu' ]))))
                    ? $this->router->generateUrl('block.create.list', [
                        'theme'   => $theme,
                        'section' => $section
                    ])
                    : null
            ]);
        }

        $themeName = $theme === 'admin'
            ? $this->tpl->getThemeAdminName()
            : $this->tpl->getThemePublicName();

        $response->addStyle(
            'template.theme',
            $this->style->getUrlStyle($themeName)
        );
    }

    private function isAdmin(): bool
    {
        return in_array($this->router->getPathFromRequest(), [
                '/admin/theme/public/section',
                '/admin/theme/admin/section'
            ]) && $this->core->callHook('app.granted', [ 'block.administer' ]);
    }

    private function getNameTheme(): string
    {
        return $this->tpl->isTheme(Templating::THEME_PUBLIC)
            ? 'public'
            : 'admin';
    }

    private function getBlocks(string $theme, bool $isAdmin): array
    {
        /** @phpstan-var BlockEntity[] $blocks */
        $blocks = $this->query
            ->from('block')
            ->where('theme', '=', $theme)
            ->orderBy('weight')
            ->fetchAll();

        $listBlock = $this->block->getBlocks();

        $out = [];
        foreach ($blocks as $block) {
            if (!$isAdmin && (!$this->isVisibilityPages($block) || !$this->isVisibilityRoles($block))) {
                continue;
            }
            if (!empty($block[ 'key_block' ])) {
                $tplBlock = $this->tpl->createBlock(
                    $listBlock[ $block[ 'key_block' ] ][ 'tpl' ],
                    $listBlock[ $block[ 'key_block' ] ][ 'path' ]
                );

                /* Construit les options avec les option présentent dans le bloc et les données en base. */
                $options = array_merge(
                    $listBlock[ $block[ 'key_block' ] ][ 'options' ] ?? [],
                    $this->block->decodeOptions($block[ 'options' ])
                );

                /** @var string|ServicesBlock $content */
                $content = $this->core->callHook(
                    "block.{$block[ 'hook' ]}",
                    [ $tplBlock, $options ]
                );
                $block[ 'content' ] .= (string) $content;
            }
            if ($isAdmin) {
                $params = [
                    'theme' => $theme,
                    'id'    => $block[ 'block_id' ]
                ];

                $block[ 'link_edit' ]       = $this->router->generateUrl('block.edit', $params);
                $block[ 'link_edit_style' ] = $this->router->generateUrl('block.style.edit', $params);
                $block[ 'link_remove' ]     = $this->router->generateUrl('block.remove', $params);
                $block[ 'link_update' ]     = $this->router->generateUrl('block.section.update', $params);
                $block[ 'title_admin' ]     = empty($block[ 'key_block' ])
                    ? ''
                    : $listBlock[ $block[ 'key_block' ] ][ 'title' ] ?? '';
            }
            $out[ $block[ 'section' ] ][] = $block;
        }

        return $out;
    }

    private function isVisibilityPages(array $block): bool
    {
        $pagesTrim = trim($block[ 'pages' ]);
        if ($pagesTrim === '') {
            return true;
        }

        $path = $this->router->getPathFromRequest();

        $visibility = $block[ 'visibility_pages' ];
        $pages      = explode(PHP_EOL, $pagesTrim);

        foreach ($pages as $page) {
            $page = '/' . trim($page, " \n\r\t\v\x00/");

            if ($page === $path) {
                return $visibility;
            }
            $str     = preg_quote($page, '/');
            $pattern = strtr($str, [ '%' => '.*' ]);
            if (preg_match("/^$pattern$/", $path)) {
                return $visibility;
            }
        }

        return !$visibility;
    }

    private function isVisibilityRoles(array $block): bool
    {
        $rolesBlock = explode(',', $block[ 'roles' ]);
        $visibility = $block[ 'visibility_roles' ];

        /* S'il n'y a pas d'utilisateur et que l'on demande de suivre les utilisateurs non connectés. */
        if (!$this->userCurrent && in_array(1, $rolesBlock)) {
            return $visibility;
        }

        foreach ($rolesBlock as $analyticsRole) {
            foreach ($this->roles as $role) {
                if ($analyticsRole == $role[ 'role_id' ]) {
                    return $visibility;
                }
            }
        }

        return !$visibility;
    }
}
