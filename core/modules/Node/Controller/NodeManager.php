<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Paginate\Paginator;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Template\Services\Block;

/**
 * @method \SoosyzeCore\Node\Services\NodeUser       nodeuser()
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 * @method \SoosyzeCore\User\Services\User           user()
 */
class NodeManager extends \Soosyze\Controller
{
    /**
     * @var int
     */
    private static $limit = 5;

    /**
     * @var bool
     */
    private $admin = false;

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin(ServerRequestInterface $req): ResponseInterface
    {
        $requestNodeAdd = self::router()->generateRequest('node.add');
        $linkAdd        = $this->container->callHook('app.granted.request', [ $requestNodeAdd ])
            ? $requestNodeAdd->getUri()
            : null;

        $this->admin = true;

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Contents')
                ])
                ->make('page.content', 'node/content-node_manager-admin.php', $this->pathViews, [
                    'action_filter'         => self::router()->generateUrl('node.filter'),
                    'link_add'              => $linkAdd,
                    'link_index'            => self::router()->generateUrl('node.admin'),
                    'link_search_status'    => self::router()->generateUrl('node.status.search'),
                    'link_search_node_type' => self::router()->generateUrl('node.type.search'),
                ])
                ->addBlock('content.table', $this->filter($req));
    }

    public function filter(ServerRequestInterface $req, int $pageId = 1): Block
    {
        $validator = (new Validator())
            ->setRules([
                'title'          => '!required|string|max:255',
                'types'          => '!required|array',
                'node_status_id' => '!required|array'
            ])
            ->setInputs($req->getQueryParams());

        $query  = self::nodeuser()->getNodesQuery();
        $userId = null;

        if ($user = self::user()->isConnected()) {
            $userId = $user[ 'user_id' ];
        }

        self::nodeuser()
            ->whereNodes($query)
            ->orWhereNodesUser($query, $userId)
            ->orderNodes($query, $req);

        $params = [];

        if ($validator->isValid()) {
            $titleInput = $validator->getInputString('title');
            if (!empty($titleInput)) {
                $params[ 'title' ] = $titleInput;
                self::nodeuser()->title = $titleInput;
                $query->where('title', 'ilike', '%' . $titleInput . '%');
            }

            $typesInput = $validator->getInputArray('types');
            if (!empty($typesInput)) {
                $params[ 'types' ] = $typesInput;
                $query->in('type', $typesInput);
            }

            $nodeStatusIdInput = $validator->getInputArray('node_status_id');
            if (!empty($nodeStatusIdInput)) {
                $params[ 'node_status_id' ] = $nodeStatusIdInput;
                $query->in('node_status_id', $nodeStatusIdInput);
            }
        }

        $paramsDateChangedSort = $params;
        $paramsStatusSort      = $params;
        $paramsTitleSort       = $params;
        $paramsTypeSort        = $params;

        /* Met en forme les donnes du tableau. */
        $data      = $query->fetchAll();
        $countData = count($data);
        $nodes     = array_slice($data, self::$limit * ($pageId - 1), self::$limit);
        unset($data);

        self::nodeuser()->hydrateNodesLinks($nodes);

        list($orderBy, $sort, $sortInverse, $isSortAsc) = $this->getSortParams($req);
        $params[ 'order_by' ] = $orderBy;
        $params[ 'sort' ]     = $sort;

        /* Liens */
        $linkPagination = self::router()->generateRequest('node.filter.page', [], false)->getUri();
        $linkSort       = self::router()->generateRequest('node.filter')->getUri();

        if ($params !== []) {
            $linkPagination = $linkPagination->withQuery(
                http_build_query($params)
            );
        }

        $paramsDateChangedSort += [
            'order_by' => 'date_changed',
            'sort'     => $sortInverse
        ];

        $paramsStatusSort += [
            'order_by' => 'node_status_id',
            'sort'     => $sortInverse
        ];

        $paramsTitleSort += [
            'order_by' => 'title',
            'sort'     => $sortInverse
        ];

        $paramsTypeSort += [
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
                    'link_title_sort'        => $linkSort->withQuery(http_build_query($paramsTitleSort)),
                    'link_type_sort'         => $linkSort->withQuery(http_build_query($paramsTypeSort)),
                    'nodes'                  => $nodes,
                    'order_by'               => $orderBy,
                    'paginate'               => (new Paginator($countData, self::$limit, $pageId, (string) $linkPagination))
                        ->setKey('%7BpageId%7D')
        ]);
    }

    private function getSortParams(ServerRequestInterface $req): array
    {
        $get = $req->getQueryParams();

        $orderBy = !empty($get[ 'order_by' ]) && in_array($get[ 'order_by' ], [
                'date_changed', 'node_status_id', 'title', 'type'
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
