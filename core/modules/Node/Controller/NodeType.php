<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Node\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query query()
 *
 * @phpstan-import-type NodeTypeEntity from \Soosyze\Core\Modules\Node\Extend
 */
class NodeType extends \Soosyze\Controller
{
    public function search(): ResponseInterface
    {
        /** @phpstan-var array<NodeTypeEntity> $data */
        $data = self::query()
            ->from('node_type')
            ->fetchAll();

        $out = [];
        foreach ($data as $value) {
            $out[] = [
                'id'   => $value[ 'node_type' ],
                'text' => t($value[ 'node_type_name' ])
            ];
        }

        return $this->json(200, [ 'results' => $out ]);
    }
}
