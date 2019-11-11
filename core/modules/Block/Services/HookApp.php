<?php

namespace SoosyzeCore\Block\Services;

class HookApp
{
    protected $tpl;

    protected $core;

    protected $query;

    protected $user;

    public function __construct($template, $core, $query, $user)
    {
        $this->tpl       = $template;
        $this->core      = $core;
        $this->query     = $query;
        $this->user      = $user;
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function hookResponseAfter(
    \Soosyze\Components\Http\ServerRequest $request,
        &$response
    ) {
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $isAdmin = in_array($request->getUri()->getQuery(), [
            'q=admin/section/theme', 'q=admin/section/theme_admin'
        ]);
        $blocks  = $this->getBlocks($request, $isAdmin);

        $sections = $this->tpl->getSections();
        foreach ($sections as $section) {
            $response->make('page.' . $section, 'section.php', $this->pathViews, [
                'section_id'  => $section,
                'content'     => !empty($blocks[ $section ])
                    ? $blocks[ $section ]
                    : [],
                'edit'        => $isAdmin,
                'link_create' => $this->core->get('router')->getRoute('block.create', [
                    ':section' => $section ])
            ]);
        }
    }

    protected function getBlocks($request, $isAdmin)
    {
        $blocks = $this->query
            ->from('block')
            ->orderBy('weight')
            ->fetchAll();
        $listBlock = $this->core->get('block')->getBlocks();
        $out    = [];
        foreach ($blocks as $block) {
            if (!$isAdmin && (!$this->isVisibilityPages($block, $request) || !$this->isVisibilityRoles($block))) {
                continue;
            }
            if (!empty($block[ 'hook' ])) {
                $tpl = $this->tpl->createBlock($listBlock[$block['hook']][ 'tpl' ], $listBlock[$block['hook']][ 'path' ]);
                $block['content'] .= (string) $this->core->callHook('block.' . $block['hook'], [$tpl]);
            }
            if ($isAdmin) {
                $block[ 'link_edit' ]   = $this->core->get('router')->getRoute('block.edit', [
                    ':id' => $block[ 'block_id' ] ]);
                $block[ 'link_delete' ] = $this->core->get('router')->getRoute('block.delete', [
                    ':id' => $block[ 'block_id' ] ]);
                $block[ 'link_update' ] = $this->core->get('router')->getRoute('section.update', [
                    ':id' => $block[ 'block_id' ] ]);
            }
            $out[ $block[ 'section' ] ][] = $block;
        }

        return $out;
    }

    protected function isVisibilityPages(array $block, $request)
    {
        $uri = $request->getUri();
        parse_str($uri->getQuery(), $query);

        $path = empty($query[ 'q' ])
            ? '/'
            : $query[ 'q' ];
        
        $visibility = $block[ 'visibility_pages' ];
        $pages      = $block[ 'pages' ];

        foreach (explode(PHP_EOL, $pages) as $page) {
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
        $user        = $this->user->isConnected();
        $roles_block = explode(',', $block[ 'roles' ]);
        $visibility  = $block[ 'visibility_roles' ];

        /* S'il n'y a pas d'utilisateur et que l'on demande de suivre les utilisateurs non connectés. */
        if (!$user && in_array(1, $roles_block)) {
            return $visibility;
        }

        $roles = $this->user->getRolesUser($user[ 'user_id' ]);

        foreach ($roles_block as $analytics_role) {
            foreach ($roles as $role) {
                if ($analytics_role == $role[ 'role_id' ]) {
                    return $visibility;
                }
            }
        }

        return !$visibility;
    }
}
