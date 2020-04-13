<?php

namespace SoosyzeCore\News\Services;

class HookNode
{
    public function hookNodeEntityPictureShow(&$entity)
    {
        $entity->pathOverride(dirname(__DIR__) . '/Views/');
    }

    public function hookNodeStoreBefore($validator, &$fieldsInsert, $type)
    {
        if ($type === 'article') {
            $words = str_word_count(strip_tags($fieldsUpdate['body']));
            $fieldsUpdate[ 'reading_time' ] = ceil($words / 200);
        }
    }

    public function hookNodeUpdateBefore(
        $validator,
        &$fieldsUpdate,
        $node,
        $id_node
    ) {
        if ($node['type'] === 'article') {
            $words = str_word_count(strip_tags($fieldsUpdate['body']));
            $fieldsUpdate[ 'reading_time' ] = ceil($words / 200);
        }
    }
}
