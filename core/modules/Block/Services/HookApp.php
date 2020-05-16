<?php

namespace SoosyzeCore\Block\Services;

class HookApp
{
    protected $tpl;

    protected $core;

    protected $query;

    protected $user;

    public function __construct($template, $core, $query, $user, $router)
    {
        $this->tpl       = $template;
        $this->core      = $core;
        $this->query     = $query;
        $this->user      = $user;
        $this->router    = $router;
        $this->pathViews = dirname(__DIR__) . '/Views/';
        
        $this->userCurrent = $this->user->isConnected();
        $this->roles = $this->userCurrent
            ? $this->user->getRolesUser($this->userCurrent[ 'user_id' ])
            : [];
    }

    public function hookResponseAfter(
        \Soosyze\Components\Http\ServerRequest $request,
        &$response
    ) {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $theme   = $this->getNameTheme();
        $isAdmin = $this->core->callHook('app.granted', [ 'block.administer' ]) && !empty($theme);

        $blocks = $this->getBlocks($request, $isAdmin);

        $sections   = $this->tpl->getSections();
        $linkCreate = '';

        foreach ($sections as $section) {
            if ($isAdmin) {
                $linkCreate = $this->router->getRoute('block.create', [
                    ':theme'   => $theme,
                    ':section' => $section
                ]);
            }
            $response->make('page.' . $section, 'section.php', $this->pathViews, [
                'section_id'  => $section,
                'content'     => !empty($blocks[ $section ])
                    ? $blocks[ $section ]
                    : [],
                'is_admin'    => $isAdmin,
                'link_create' => $linkCreate
            ]);
        }
    }
    
    protected function getNameTheme()
    {
        $query = $this->router->parseQueryFromRequest();
        if ($query === 'admin/section/theme') {
            return 'theme';
        }
        if ($query === 'admin/section/theme_admin') {
            return 'theme_admin';
        }

        return '';
    }

    protected function getBlocks($request, $isAdmin)
    {
        $blocks    = $this->query
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
                    ':id' => $block[ 'block_id' ] ]);
                $block[ 'link_delete' ] = $this->router->getRoute('block.delete', [
                    ':id' => $block[ 'block_id' ] ]);
                $block[ 'link_update' ] = $this->router->getRoute('section.update', [
                    ':id' => $block[ 'block_id' ] ]);
            }
            $out[ $block[ 'section' ] ][] = $block;
        }

        return $out;
    }

    protected function isVisibilityPages(array $block)
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

    protected function isVisibilityRoles($block)
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
