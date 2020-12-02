<?php

namespace SoosyzeCore\Node\Controller;

use Soosyze\Components\Paginate\Paginator;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class NodeManager extends \Soosyze\Controller
{
    protected static $limit = 20;

    protected static $page = 1;

    protected $admin       = false;

    protected $pathViews;

    protected $title = '';

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin($req)
    {
        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        $requestNodeAdd = self::router()->getRequestByRoute('node.add');
        $linkAdd        = $this->container->callHook('app.granted.route', [ $requestNodeAdd ])
            ? $requestNodeAdd->getUri()
            : null;

        /* Liens */
        $linkIndex  = self::router()->getRequestByRoute('node.admin')->getUri();
        $linkFilter = self::router()->getRequestByRoute('node.filter')->getUri();

        $this->admin = true;

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('My contents')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node/content-node_manager-admin.php', $this->pathViews, [
                    'action_filter'         => $linkFilter,
                    'link_add'              => $linkAdd,
                    'link_index'            => $linkIndex,
                    'link_search_status'    => self::router()->getRoute('node.status.search'),
                    'link_search_node_type' => self::router()->getRoute('node.type.search'),
                ])
                ->addBlock('content.table', $this->filterPage(1, $req));
    }

    public function filter($req)
    {
        return $this->filterPage(1, $req);
    }

    public function filterPage($page, $req)
    {
        if (!$req->isAjax() && !$this->admin) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'title'          => '!required|string|max:255',
                'types'          => '!required|array',
                'node_status_id' => '!required|array'
            ])
            ->setInputs($req->getQueryParams());

        $query = $this->getNodes($req);

        $params = [];
        if ($validator->getInput('title', '')) {
            $params[ 'title' ] = $validator->getInput('title');
            $this->title = $validator->getInput('title');
            $query->where('title', 'ilike', '%' . $validator->getInput('title') . '%');
        }
        if ($validator->getInput('types', [])) {
            $params[ 'types' ] = $validator->getInput('types');
            $query->in('type', $validator->getInput('types'));
        }
        if ($validator->getInput('node_status_id', [])) {
            $params[ 'node_status_id' ] = $validator->getInput('node_status_id');
            $query->in('node_status_id', $validator->getInput('node_status_id'));
        }

        $paramsDateChangedSort = $params;
        $paramsStatusSort      = $params;
        $paramsTypeSort        = $params;

        /* Met en forme les donnes du tableau. */
        $data      = $query->fetchAll();
        $countData = count($data);
        $nodes     = array_slice($data, self::$limit * ($page - 1), self::$limit);

        $this->hydrateNodesLinks($nodes);

        list($orderBy, $sort, $sortInverse, $isSortAsc) = $this->getSortParams($req);
        $params[ 'order_by' ] = $orderBy;
        $params[ 'sort' ]     = $sort;

        /* Liens */
        $linkPagination = self::router()->getRequestByRoute('node.filter.page', [], false)->getUri();
        $linkSort       = self::router()->getRequestByRoute('node.filter')->getUri();

        if ($params) {
            $linkPagination = $linkPagination->withQuery(
                self::router()->isRewrite()
                ? http_build_query($params)
                : $linkPagination->getQuery() . '&' . http_build_query($params)
            );
        }

        $parseQuery = [];

        parse_str($linkSort->getQuery(), $parseQuery);
        $route = self::router()->isRewrite()
            ? null
            : $parseQuery[ 'q' ];

        $paramsDateChangedSort += [
            'q'        => $route,
            'order_by' => 'date_changed',
            'sort'     => $sortInverse
        ];

        $paramsStatusSort += [
            'q'        => $route,
            'order_by' => 'node_status_id',
            'sort'     => $sortInverse
        ];

        $paramsTypeSort += [
            'q'        => $route,
            'order_by' => 'type',
            'sort'     => $sortInverse
        ];

        return self::template()
                ->createBlock('node/table-node.php', $this->pathViews)
                ->addVars([
                    'count'                  => $countData,
                    'is_admin'               => $this->admin,
                    'is_sort_asc'            => $isSortAsc,
                    'link_date_changed_sort' => $linkSort->withQuery(http_build_query($paramsDateChangedSort)),
                    'link_status_sort'       => $linkSort->withQuery(http_build_query($paramsStatusSort)),
                    'link_type_sort'         => $linkSort->withQuery(http_build_query($paramsTypeSort)),
                    'nodes'                  => $nodes,
                    'order_by'               => $orderBy,
                    'paginate'               => new Paginator($countData, self::$limit, $page, $linkPagination)
        ]);
    }

    protected function hydrateNodesLinks(&$nodes)
    {
        $nodeAdminister = $this->container->callHook('app.granted', [ 'node.administer' ]);

        foreach ($nodes as &$node) {
            $node[ 'link_view' ] = self::router()->makeRoute(
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
                $node[ 'link_clone' ] = self::router()->getRoute('node.clone', [
                    ':id_node' => $node[ 'id' ]
                ]);
            }
            if ($nodeAdminister || $this->container->callHook('app.granted', [ 'node.deleted.' . $node[ 'type' ] ])) {
                $node[ 'link_remove' ] = self::router()->getRoute('node.api.remove', [
                    ':id_node' => $node[ 'id' ]
                ]);
            }
            $node[ 'title' ] = Util::strHighlight($this->title, $node[ 'title' ]);
        }
        unset($node);
    }

    protected function getNodes(\Psr\Http\Message\ServerRequestInterface $req)
    {
        $query = clone self::query();
        $nodes = $query->from('node')
            ->leftJoin('node_type', 'type', 'node_type.node_type');

        if ($this->container->callHook('app.granted', [ 'node.administer' ])) {
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

        return $this->sortNode($req, $nodes);
    }

    protected function sortNode($req, $nodes)
    {
        $get = $req->getQueryParams();

        $nodes->orderBy('sticky', 'desc');

        if (!empty($get[ 'order_by' ]) && in_array($get[ 'order_by' ], [
                'date_changed', 'node_status_id', 'type'
            ])) {
            $sort = !isset($get[ 'sort' ]) || $get[ 'sort' ] !== 'asc'
                ? 'desc'
                : 'asc';

            return $nodes->orderBy($get[ 'order_by' ], $sort);
        }

        return $nodes->orderBy('date_changed', 'desc');
    }

    protected function getSortParams($req)
    {
        $get = $req->getQueryParams();

        $orderBy = !empty($get[ 'order_by' ]) && in_array($get[ 'order_by' ], [
                'date_changed', 'node_status_id', 'type'
            ])
            ? $get[ 'order_by' ]
            : null;

        $sort = !isset($get[ 'sort' ]) || $get[ 'sort' ] !== 'asc'
            ? 'desc'
            : 'asc';

        $sortInverse = $sort === 'asc'
            ? 'desc'
            : 'asc';

        return [ $orderBy, $sort, $sortInverse, $sort === 'asc' ];
    }
}
