<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Node\Hook;

use Soosyze\Components\Router\Router;
use Soosyze\Components\Validator\Validator;
use Soosyze\Config;
use Soosyze\Core\Modules\Node\Services\Node;
use Soosyze\Core\Modules\Node\Services\NodeUser as ServiceNodeUser;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;
use Soosyze\Core\Modules\Template\Services\Templating;
use Soosyze\Core\Modules\User\Hook\Config as UserConfig;
use Soosyze\Core\Modules\User\Services\User;

/**
 * @phpstan-import-type NodeEntity from \Soosyze\Core\Modules\Node\Extend
 */
class NodeUser
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Node
     */
    private $node;

    /**
     * @var ServiceNodeUser
     */
    private $nodeuser;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Templating
     */
    private $template;

    /**
     * @var User
     */
    private $user;

    public function __construct(
        Config $config,
        Node $node,
        ServiceNodeUser $nodeuser,
        Query $query,
        Router $router,
        Templating $template,
        User $user
    ) {
        $this->config   = $config;
        $this->node     = $node;
        $this->nodeuser = $nodeuser;
        $this->query    = $query;
        $this->router   = $router;
        $this->template = $template;
        $this->user     = $user;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function hookUserShow(array &$contentUser, array $user): void
    {
        $nodesQuery = $this->nodeuser->getNodesQuery();

        $this->nodeuser->whereNodes($nodesQuery);

        $nodes = $nodesQuery
            ->where('user_id', '=', $user[ 'user_id' ])
            ->limit(20)
            ->fetchAll();

        $this->nodeuser->hydrateNodesLinks($nodes);

        $requestLinkAdd = $this->router->generateRequest('node.add');

        $contentNothing = 'The user has no content at the moment.';
        if (($userConnected  = $this->user->isConnected()) && $userConnected[ 'user_id' ] == $user[ 'user_id' ]) {
            $contentNothing = 'Your account has no content at the moment.';
        }

        $contentUser[] = $this->template
            ->createBlock('components/user/content_user-nodes.php', $this->pathViews)
            ->addVars([
            'content_nothing' => $contentNothing,
            'link_add'        => $this->user->isGrantedRequest($requestLinkAdd)
                ? $requestLinkAdd->getUri()
                : null,
            'nodes'           => $nodes
        ]);
    }

    public function hookUserDeleteAfter(Validator $validator, array $user, int $userId): void
    {
        $userDelete = $this->config->get('settings.user_delete', UserConfig::DELETE_ACCOUNT);

        if ($userDelete === UserConfig::DELETE_ACCOUNT) {
            $this->query
                ->delete()
                ->from('node')
                ->where('user_id', '=', $userId)
                ->execute();
        } else {
            /** @phpstan-var array<NodeEntity> $nodes */
            $nodes = $this->query
                ->from('node')
                ->where('user_id', '=', $userId)
                ->fetchAll();

            foreach ($nodes as $node) {
                $this->node->deleteRelation($node);
                $this->node->deleteFile($node[ 'type' ], $node[ 'id' ]);
            }

            $this->query
                ->update('node', [
                    'user_id' => null
                ])
                ->where('user_id', '=', $userId)
                ->execute();
        }
    }
}
