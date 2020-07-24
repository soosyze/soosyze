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

    public function index($req)
    {
        return $this->page(1, $req);
    }

    public function page($page, $req)
    {
        $nodes = $this->getNodes($page)->fetchAll();

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

        $link = self::router()->getRoute('node.page', [], false);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('My contents')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-admin.php', $this->pathViews, [
                    'action_filter'         => self::router()->getRoute('node.filter'),
                    'link_add'              => self::router()->getRoute('node.add'),
                    'link_index'            => self::router()->getRoute('node.index'),
                    'link_search_status'    => self::router()->getRoute('node.status.search'),
                    'link_search_node_type' => self::router()->getRoute('node.type.search'),
                    'nodes'                 => $nodes,
                    'paginate'              => new Paginator(count($queryAll), self::$limit, $page, $link)
        ]);
    }

    public function filter($req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $validator = (new Validator)
            ->setRules([
                'title'          => '!required|string|max:255',
                'types'          => '!required|array',
                'node_status_id' => '!required|array'
            ])
            ->setInputs($req->getQueryParams());

        $nodes = $this->getNodes(1);

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
                ->createBlock('filter-node.php', $this->pathViews)
                ->addVars([
                    'nodes' => $data
        ]);
    }

    protected function hydrateNodesLinks(&$nodes)
    {
        $nodeAdminister = $this->container->callHook('app.granted', [ 'node.administer' ]);

        foreach ($nodes as &$node) {
            $node[ 'link_view' ]   = self::router()->getRoute('node.show', [
                ':id_node' => $node[ 'id' ]
            ]);
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
    }
    
    protected function getNodes($page)
    {
        $nodes = clone self::query()->from('node');

        if ($this->container->callHook('app.granted', [ 'node.administer' ])) {
            return $nodes
                    ->orderBy('sticky', 'desc')
                    ->orderBy('date_changed', 'desc')
                    ->limit(self::$limit, self::$limit * ($page - 1));
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

        return $nodes
                ->orderBy('sticky', 'desc')
                ->orderBy('date_changed', 'desc')
                ->limit(self::$limit, self::$limit * ($page - 1));
    }
}
