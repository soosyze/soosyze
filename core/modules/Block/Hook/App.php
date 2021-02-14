<?php

namespace SoosyzeCore\Block\Hook;

use SoosyzeCore\Template\Services\Templating;

class App
{
    /**
     * @var \Soosyze\App
     */
    private $core;

    /**
     * @var \SoosyzeCore\QueryBuilder\Services\Query
     */
    private $query;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @var \Soosyze\Components\Router\Router
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

    public function __construct($core, $query, $router, $template, $user)
    {
        $this->core   = $core;
        $this->query  = $query;
        $this->router = $router;
        $this->tpl    = $template;

        $this->pathViews = dirname(__DIR__) . '/Views/';

        $this->userCurrent = $user->isConnected();

        if ($this->userCurrent) {
            $this->roles   = $user->getRolesUser($this->userCurrent[ 'user_id' ]);
            $this->roles[] = [ 'role_id' => 2 ];
        }
    }

    public function hookResponseAfter($request, &$response)
    {
        if (!($response instanceof Templating) || $response->getStatusCode() !== 200) {
            return;
        }

        $theme   = $this->getNameTheme();
        $isAdmin = $this->core->callHook('app.granted', [ 'block.administer' ]) && !empty($theme);

        $blocks = $this->getBlocks($isAdmin);

        $sections = $this->tpl->getSections();

        foreach ($sections as $section) {
            $response->make('page.' . $section, 'section.php', $this->pathViews, [
                'section_id'  => $section,
                'content'     => !empty($blocks[ $section ])
                    ? $blocks[ $section ]
                    : [],
                'is_admin'    => $isAdmin,
                'link_create' => $isAdmin
                    ? $this->router->getRoute('block.create', [
                        ':theme'   => $theme,
                        ':section' => $section
                    ])
                    : ''
            ]);
        }
    }

    private function getNameTheme()
    {
        $query = $this->router->parseQueryFromRequest();
        if ($query === 'admin/theme/public/section') {
            return 'public';
        }
        if ($query === 'admin/theme/admin/section') {
            return 'admin';
        }

        return '';
    }

    private function getBlocks($isAdmin)
    {
        $blocks = $this->query
            ->from('block')
            ->orderBy('weight')
            ->fetchAll();

        $listBlock = $this->core->get('block')->getBlocks();

        $out = [];
        foreach ($blocks as $block) {
            if (!$isAdmin && (!$this->isVisibilityPages($block) || !$this->isVisibilityRoles($block))) {
                continue;
            }
            if (!empty($block[ 'hook' ])) {
                $tplBlock           = $this->tpl->createBlock(
                    $listBlock[ $block[ 'key_block' ] ][ 'tpl' ],
                    $listBlock[ $block[ 'key_block' ] ][ 'path' ]
                );
                $block[ 'content' ] .= (string) $this->core->callHook('block.' . $block[ 'hook' ], [
                        $tplBlock, empty($block[ 'options' ])
                        ? []
                        : json_decode($block[ 'options' ], true)
                ]);
            }
            if ($isAdmin) {
                $block[ 'link_edit' ]   = $this->router->getRoute('block.edit', [
                    ':id' => $block[ 'block_id' ]
                ]);
                $block[ 'link_delete' ] = $this->router->getRoute('block.delete', [
                    ':id' => $block[ 'block_id' ]
                ]);
                $block[ 'link_update' ] = $this->router->getRoute('block.section.update', [
                    ':id' => $block[ 'block_id' ]
                ]);
            }
            $out[ $block[ 'section' ] ][] = $block;
        }

        return $out;
    }

    private function isVisibilityPages(array $block)
    {
        $path = $this->router->parseQueryFromRequest();

        $visibility = $block[ 'visibility_pages' ];
        $pages      = explode(PHP_EOL, $block[ 'pages' ]);

        foreach ($pages as $page) {
            $page = trim($page);
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

    private function isVisibilityRoles(array $block)
    {
        $rolesBlock  = explode(',', $block[ 'roles' ]);
        $visibility  = $block[ 'visibility_roles' ];

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
