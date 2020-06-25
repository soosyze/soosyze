<?php

namespace SoosyzeCore\Node\Controller;

class NodeType extends \Soosyze\Controller
{
    public function search()
    {
        $data = self::query()
            ->from('node_type')
            ->fetchAll();

        $out = [];
        foreach ($data as $value) {
            $out[] = [
                'id'   => $value[ 'node_type' ],
                'text' => $value[ 'node_type_name' ]
            ];
        }

        return $this->json(200, [ 'results' => $out ]);
    }
}
