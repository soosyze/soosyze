<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Node\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query query()
 *
 * @phpstan-import-type NodeStatusEntity from \Soosyze\Core\Modules\Node\Extend
 */
class NodeStatus extends \Soosyze\Controller
{
    public function search(): ResponseInterface
    {
        /** @phpstan-var array<NodeStatusEntity> $data */
        $data = self::query()
            ->from('node_status')
            ->fetchAll();

        $out = [];
        foreach ($data as $value) {
            $out[] = [
                'id'   => $value[ 'node_status_id' ],
                'text' => t($value[ 'node_status_name' ])
            ];
        }

        return $this->json(200, [ 'results' => $out ]);
    }
}
