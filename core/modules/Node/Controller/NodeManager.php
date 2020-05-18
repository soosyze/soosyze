<?php

namespace SoosyzeCore\Node\Controller;

use Soosyze\Components\Paginate\Paginator;

class NodeManager extends \Soosyze\Controller
{
    public static $limit = 20;

    protected $pathViews;

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin( $req )
    {
        return $this->adminPage(1, $req);
    }

    public function adminPage( $page, $req )
    {
        $offset = self::$limit * ($page - 1);
        $nodes  = self::query()
            ->from('node')
            ->where('node_status_id', '!=', 4)
            ->orderBy('date_changed', 'desc')
            ->limit(self::$limit, $offset)
            ->fetchAll();

        if( !$nodes && $page !== 1 )
        {
            return $this->get404($req);
        }

        foreach( $nodes as &$node )
        {
            $node[ 'link_view' ]   = self::router()->getRoute('node.show', [
                ':id_node' => $node[ 'id' ]
            ]);
            $node[ 'link_edit' ]   = self::router()->getRoute('node.edit', [
                ':id_node' => $node[ 'id' ]
            ]);
            $node[ 'link_clone' ]  = self::router()->getRoute('node.clone', [
                ':id_node' => $node[ 'id' ]
            ]);
            $node[ 'link_delete' ] = self::router()->getRoute('node.delete', [
                ':id_node' => $node[ 'id' ]
            ]);
        }

        $messages = [];
        if( isset($_SESSION[ 'messages' ]) )
        {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        $queryAll = self::query()
            ->from('node')
            ->fetchAll();
        $link     = self::router()->getRoute('node.page', [], false);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('My contents')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-admin.php', $this->pathViews, [
                    'action_search'         => self::router()->getRoute('node.search'),
                    'link_add'              => self::router()->getRoute('node.add'),
                    'link_search_status'    => self::router()->getRoute('node.status.search'),
                    'link_search_node_type' => self::router()->getRoute('node.type.search'),
                    'nodes'                 => $nodes,
                    'paginate'              => new Paginator(count($queryAll), self::$limit, $page, $link)
        ]);
    }
}