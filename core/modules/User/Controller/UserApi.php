<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\User\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query query()
 *
 * @phpstan-import-type UserEntity from \Soosyze\Core\Modules\User\Extend
 */
class UserApi extends \Soosyze\Controller
{
    public function select(ServerRequestInterface $req): ResponseInterface
    {
        /** @phpstan-var array<UserEntity> $data */
        $data = self::query()
            ->from('user')
            ->fetchAll();

        $out = [];
        foreach ($data as $value) {
            $out[] = [
                'id'   => $value[ 'user_id' ],
                'text' => t($value[ 'username' ])
            ];
        }

        return $this->json(200, [ 'results' => $out ]);
    }
}
