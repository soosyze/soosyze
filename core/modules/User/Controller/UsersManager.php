<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\User\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Paginate\Paginator;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;
use Soosyze\Core\Modules\Template\Services\Block;

/**
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query  query()
 * @method \Soosyze\Core\Modules\Template\Services\Templating template()
 * @method \Soosyze\Core\Modules\User\Services\User           user()
 *
 * @phpstan-import-type UserEntity from \Soosyze\Core\Modules\User\Extend
 */
class UsersManager extends \Soosyze\Controller
{
    /**
     * @var int
     */
    private static $limit = 20;

    /**
     * @var string
     */
    private $username = '';

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin(ServerRequestInterface $req): ResponseInterface
    {
        $block = $this->filter($req);
        if ($block instanceof ResponseInterface) {
            return $block;
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Administer users')
                ])
                ->view('page.submenu', self::user()->getUserManagerSubmenu('user.admin'))
                ->make('page.content', 'user/content-user_manager-admin.php', $this->pathViews, [
                    'link_create_user' => self::router()->generateUrl('user.create'),
                    'link_filter_user' => self::router()->generateUrl('user.filter'),
                    'link_user_admin'  => self::router()->generateUrl('user.admin')
                ])
                ->addBlock('content.table', $block);
    }

    /**
     * @return Block|ResponseInterface
     */
    public function filter(ServerRequestInterface $req, int $pageId = 1)
    {
        $validator = (new Validator())
            ->setRules([
                'actived'   => '!required|between_numeric:0,1',
                'firstname' => '!required|string|max:255',
                'name'      => '!required|string|max:255',
                'username'  => '!required|string|max:255',
            ])
            ->setInputs($req->getQueryParams());

        self::query()->from('user');

        $params = [];
        if (in_array($validator->getInput('actived'), [ '0', 0, '1', 1 ], true)) {
            $params[ 'actived' ] = $validator->getInput('actived');
            self::query()->where('actived', '=', (bool) $validator->getInput('actived'));
        }
        self::query()->whereGroup(function ($query) use ($validator, &$params): void {
            /** @phpstan-var string $firstname */
            $firstname = $validator->getInput('firstname');
            if (!empty($firstname)) {
                $params[ 'firstname' ] = $firstname;
                $query->orWhere('firstname', 'ilike', '%' . $firstname . '%');
            }
            /** @phpstan-var string $name */
            $name = $validator->getInput('name');
            if (!empty($name)) {
                $params[ 'name' ] = $name;
                $query->orWhere('name', 'ilike', '%' . $name . '%');
            }
            /** @phpstan-var string $username */
            $username = $validator->getInput('username');
            if (!empty($username)) {
                $params[ 'username' ] = $username;
                $this->username       = $username;
                $query->orWhere('username', 'ilike', '%' . $username . '%');
            }
        });

        $paramsActivedSort       = $params;
        $paramsUsernameSort      = $params;
        $paramsTimeAccessSort    = $params;
        $paramsTimeInstalledSort = $params;

        $this->sortUser(self::query(), $req);

        /** @phpstan-var array<UserEntity> $data */
        $data = self::query()->fetchAll();

        $countData = count($data);
        $users     = array_slice($data, self::$limit * ($pageId - 1), self::$limit);

        $this->hydrateUsersLinks($users);

        list($orderBy, $sort, $sortInverse, $isSortAsc) = $this->getSortParams($req);
        $params[ 'order_by' ] = $orderBy;
        $params[ 'sort' ]     = $sort;

        /* Liens */
        $linkPagination = self::router()->generateRequest('user.filter.page', [], false)->getUri();
        $linkSort       = self::router()->generateRequest('user.filter')->getUri();

        if ($params !== []) {
            $linkPagination = $linkPagination->withQuery(
                http_build_query($params)
            );
        }

        $paramsActivedSort += [
            'order_by' => 'actived',
            'sort'     => $sortInverse
        ];

        $paramsTimeAccessSort += [
            'order_by' => 'time_access',
            'sort'     => $sortInverse
        ];

        $paramsTimeInstalledSort += [
            'order_by' => 'time_installed',
            'sort'     => $sortInverse
        ];

        $paramsUsernameSort += [
            'order_by' => 'username',
            'sort'     => $sortInverse
        ];

        return self::template()
                ->createBlock('user/table-user.php', $this->pathViews)
                ->addVars([
                    'count'                    => $countData,
                    'is_sort_asc'              => $isSortAsc,
                    'link_actived_sort'        => $linkSort->withQuery(http_build_query($paramsActivedSort)),
                    'link_username_sort'       => $linkSort->withQuery(http_build_query($paramsUsernameSort)),
                    'link_time_access_sort'    => $linkSort->withQuery(http_build_query($paramsTimeAccessSort)),
                    'link_time_installed_sort' => $linkSort->withQuery(http_build_query($paramsTimeInstalledSort)),
                    'order_by'                 => $orderBy,
                    'paginate'                 => (new Paginator($countData, self::$limit, $pageId, (string) $linkPagination))
                        ->setKey('%7BpageId%7D'),
                    'users'                    => $users
        ]);
    }

    private function sortUser(Query $users, ServerRequestInterface $req): Query
    {
        $get = $req->getQueryParams();

        if (in_array($get[ 'order_by' ] ?? [], [
                'actived', 'time_access', 'time_installed', 'username'
            ])) {
            $sort = !isset($get[ 'sort' ]) || $get[ 'sort' ] !== 'asc'
                ? SORT_DESC
                : SORT_ASC;

            return $users->orderBy($get[ 'order_by' ], $sort);
        }

        return $users->orderBy('time_access', SORT_DESC);
    }

    private function hydrateUsersLinks(array &$users): void
    {
        foreach ($users as &$user) {
            $user[ 'link_show' ]   = self::router()->generateUrl('user.show', [
                'id' => $user[ 'user_id' ]
            ]);
            $user[ 'link_edit' ]   = self::router()->generateUrl('user.edit', [
                'id' => $user[ 'user_id' ]
            ]);
            $user[ 'link_remove' ] = self::router()->generateUrl('user.remove', [
                'id' => $user[ 'user_id' ]
            ]);
            $user[ 'roles' ]       = self::user()->getRolesUser($user[ 'user_id' ]);
            $user[ 'username' ]    = Util::strHighlight($this->username, $user[ 'username' ]);
        }
        unset($user);
    }

    private function getSortParams(ServerRequestInterface $req): array
    {
        $get = $req->getQueryParams();

        $orderBy = !empty($get[ 'order_by' ]) && in_array($get[ 'order_by' ], [
                'actived', 'time_access', 'time_installed', 'username'
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
