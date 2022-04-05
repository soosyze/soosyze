<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * @method \SoosyzeCore\QueryBuilder\Services\Query query()
 *
 * @phpstan-import-type NodeStatusEntity from \SoosyzeCore\Node\Extend
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
