<?php

namespace SoosyzeCore\Node\Controller;

use Soosyze\Components\Paginate\Paginator;
use Soosyze\Components\Validator\Validator;

class NodeManager extends \Soosyze\Controller
{
    protected static $limit = 20;

    protected $pathViews;

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin($req)
    {
        return $this->page(1, $req);
    }

    public function page($page, $req)
    {
        $nodes = $this->getNodes($req, $page)
            ->where('node_status_id', '!=', 4)
            ->fetchAll();

        if (!$nodes && $page !== 1) {
            return $this->get404($req);
        }

        $this->hydrateNodesLinks($nodes);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        $queryAll = self::query()
            ->from('node')
            ->fetchAll();
        
        $requestNodeAdd = self::router()->getRequestByRoute('node.add');
        $linkAdd        = $this->container->callHook('app.granted.route', [ $requestNodeAdd ])
            ? $requestNodeAdd->getUri()
            : null;
        
        $get     = $req->getQueryParams();
        $orderBy = !empty($get[ 'order_by' ]) && in_array($get[ 'order_by' ], [
                'date_changed', 'node_status_id'
            ])
            ? $get[ 'order_by' ]
            : null;

        $sort = isset($get[ 'sort' ]) && $get[ 'sort' ] !== 'desc'
            ? 'asc'
            : 'desc';

        $sortInverse = $sort === 'asc'
            ? 'desc'
            : 'asc';

        $link = self::router()
            ->getRequestByRoute('node.page', [], false)
            ->getUri()
            ->withQuery(
                $orderBy === null
            ? ''
            : "order_by=$orderBy&sort=$sort"
            );

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('My contents')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node/content-node_manager-admin.php', $this->pathViews, [
                    'action_filter'          => self::router()->getRoute('node.filter'),
                    'link_add'               => $linkAdd,
                    'link_index'             => self::router()->getRoute('node.admin'),
                    'link_search_status'     => self::router()->getRoute('node.status.search'),
                    'link_search_node_type'  => self::router()->getRoute('node.type.search'),
                    'link_date_changed_sort' => self::router()->getRequestByRoute('node.admin')->getUri()
                    ->withQuery("order_by=date_changed&sort=$sortInverse"),
                    'link_status_sort'       => self::router()->getRequestByRoute('node.admin')->getUri()
                    ->withQuery("order_by=node_status_id&sort=$sortInverse"),
                    'nodes'                  => $nodes,
                    'order_by'               => $orderBy,
                    'paginate'               => new Paginator(count($queryAll), self::$limit, $page, (string) $link),
                    'is_sort_asc'            => $sort === 'asc'
        ]);
    }

    public function filter($req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'title'          => '!required|string|max:255',
                'types'          => '!required|array',
                'node_status_id' => '!required|array'
            ])
            ->setInputs($req->getQueryParams());

        $nodes = $this->getNodes($req, 1);

        if ($validator->getInput('title', '')) {
            $nodes->where('title', 'ilike', '%' . $validator->getInput('title') . '%');
        }
        if ($validator->getInput('types', [])) {
            $nodes->in('type', $validator->getInput('types'));
        }
        if ($validator->getInput('node_status_id', [])) {
            $nodes->in('node_status_id', $validator->getInput('node_status_id'));
        }

        $data = $nodes->fetchAll();

        $this->hydrateNodesLinks($data);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('node/filter-node.php', $this->pathViews)
                ->addVars([
                    'nodes' => $data
        ]);
    }

    protected function hydrateNodesLinks(&$nodes)
    {
        $nodeAdminister = $this->container->callHook('app.granted', [ 'node.administer' ]);

        foreach ($nodes as &$node) {
            $node[ 'link_view' ]   = self::router()->makeRoute(
                'node/' . $node[ 'id' ] === self::config()->get('settings.path_index')
                    ? ''
                    : self::alias()->getAlias('node/' . $node[ 'id' ], 'node/' . $node[ 'id' ])
            );
            if ($nodeAdminister || $this->container->callHook('app.granted', [ 'node.edited.' . $node[ 'type' ] ])) {
                $node[ 'link_edit' ] = self::router()->getRoute('node.edit', [
                    ':id_node' => $node[ 'id' ]
                ]);
            }
            if ($nodeAdminister || $this->container->callHook('app.granted', [ 'node.cloned.' . $node[ 'type' ] ])) {
                $node[ 'link_clone' ]  = self::router()->getRoute('node.clone', [
                    ':id_node' => $node[ 'id' ]
                ]);
            }
            if ($nodeAdminister || $this->container->callHook('app.granted', [ 'node.deleted.' . $node[ 'type' ] ])) {
                $node[ 'link_delete' ] = self::router()->getRoute('node.delete', [
                    ':id_node' => $node[ 'id' ]
                ]);
            }
        }
        unset($node);
    }
    
    protected function getNodes(\Psr\Http\Message\ServerRequestInterface $req, $page)
    {
        $query = clone self::query();
        $nodes = $query->from('node')
            ->leftJoin('node_type', 'type', 'node_type.node_type');

        if ($this->container->callHook('app.granted', [ 'node.administer' ])) {
            $nodes
                ->orderBy('sticky', 'desc')
                ->limit(self::$limit, self::$limit * ($page - 1));

            return $this->sortNode($req, $nodes);
        }

        $publish    = $this->container->callHook('app.granted', [ 'node.show.published' ]);
        $notPublish = $this->container->callHook('app.granted', [ 'node.show.not_published' ]);

        $nodeTypes = self::query()->from('node_type')->fetchAll();
        foreach ($nodeTypes as $type) {
            $typePublish    = $publish || $this->container->callHook('app.granted', [
                'node.show.published.' . $type[ 'node_type' ]
            ]);
            $typeNotPublish = $notPublish || $this->container->callHook('app.granted', [
                'node.show.not_published.' . $type[ 'node_type' ]
            ]);

            if ($typePublish || $typeNotPublish) {
                $nodes->orWhere(function ($query) use ($type, $typePublish, $typeNotPublish) {
                    $query->where('type', $type[ 'node_type' ])
                        ->where(function ($query) use ($typePublish, $typeNotPublish) {
                            if ($typePublish) {
                                $query->where('node_status_id', '==', 1);
                            }
                            if ($typeNotPublish) {
                                $query->orWhere('node_status_id', '!=', 1);
                            }
                        });
                });
            } else {
                $nodes->where('type', '!=', $type[ 'node_type' ]);
            }
        }
        
        $nodes->orderBy('sticky', 'desc')
            ->limit(self::$limit, self::$limit * ($page - 1));

        return $this->sortNode($req, $nodes);
    }
    
    protected function sortNode($req, $nodes)
    {
        $get = $req->getQueryParams();

        if (!empty($get[ 'order_by' ]) && in_array($get[ 'order_by' ], [
                'date_changed', 'node_status_id'
            ])) {
            $sort = isset($get[ 'sort' ]) && $get[ 'sort' ] === 'desc'
                ? 'desc'
                : 'asc';
            
            return $nodes->orderBy($get[ 'order_by' ], $sort);
        }

        return $nodes->orderBy('date_changed', 'desc');
    }
}
