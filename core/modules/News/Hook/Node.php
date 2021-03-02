<?php

namespace SoosyzeCore\News\Hook;

class Node
{
    const NODE_TYPE = 'article';

    /**
     * @var \Soosyze\Config
     */
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function hookNodeShowTpl($tpl, $node, $idNode)
    {
        if ($node[ 'type' ] === self::NODE_TYPE) {
            $tpl->getBlock('page.content')->addPathOverride(dirname(__DIR__) . '/Views/');
        }
    }

    public function hookNodeStoreBefore($validator, array &$fieldsInsert, $type)
    {
        if ($type === self::NODE_TYPE) {
            $words = str_word_count(strip_tags($fieldsInsert[ 'body' ]));

            $fieldsInsert[ 'reading_time' ] = ceil($words / 200);
        }
    }

    public function hookNodeUpdateBefore(
        $validator,
        array &$fieldsUpdate,
        array $node,
        $idNode
    ) {
        if ($node[ 'type' ] === self::NODE_TYPE) {
            $words = str_word_count(strip_tags($fieldsUpdate[ 'body' ]));

            $fieldsUpdate[ 'reading_time' ] = ceil($words / 200);
        }
    }

    public function hookNodeMakefields($type, array &$fields, array &$data)
    {
        if ($type === self::NODE_TYPE && empty($data[ 'image' ])) {
            $fields[] = [
                'field_name' => 'icon',
                'field_type' => 'text'
            ];

            $data[ 'image' ] = $this->config->get('settings.new_default_image', null);
            $data[ 'icon' ]  = $this->config->get('settings.new_default_icon', null);
        }
    }

    public function hookNodeFormData(array &$content, $type)
    {
        if ($type === self::NODE_TYPE && empty($content[ 'image' ])) {
            $content[ 'image' ] = $this->config->get('settings.new_default_image', '');
        }
    }
}
