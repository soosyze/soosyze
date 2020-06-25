<?php

namespace SoosyzeCore\Node\Controller;

class NodeStatus extends \Soosyze\Controller
{
    public function search()
    {
        $data = self::query()
            ->from('node_status')
            ->fetchAll();

        $out = [];
        foreach ($data as $value) {
            $out[] = [
                'id'   => $value[ 'node_status_id' ],
                'text' => $value[ 'node_status_name' ]
            ];
        }

        return $this->json(200, [ 'results' => $out ]);
    }
}
