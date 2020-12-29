<?php

namespace SoosyzeCore\News\Services;

class HookNode
{
    /**
     * @var \Soosyze\Config
     */
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function hookNodeEntityPictureShow(&$entity)
    {
        $entity->addPathOverride(dirname(__DIR__) . '/Views/');
    }

    public function hookNodeStoreBefore($validator, &$fieldsInsert, $type)
    {
        if ($type === 'article') {
            $words = str_word_count(strip_tags($fieldsInsert[ 'body' ]));

            $fieldsInsert[ 'reading_time' ] = ceil($words / 200);
        }
    }

    public function hookNodeUpdateBefore(
        $validator,
        &$fieldsUpdate,
        $node,
        $idNode
    ) {
        if ($node[ 'type' ] === 'article') {
            $words = str_word_count(strip_tags($fieldsUpdate[ 'body' ]));

            $fieldsUpdate[ 'reading_time' ] = ceil($words / 200);
        }
    }

    public function hookNodeShowBefore($type, &$fields, &$data)
    {
        if ($type === 'article') {
            if (empty($data[ 'image' ])) {
                $fields[]      = [
                    'field_name' => 'icon',
                    'field_type' => 'text'
                ];
                $data[ 'image' ] = $this->config->get('settings.new_default_image', null);
                $data[ 'icon' ]  = $this->config->get('settings.new_default_icon', null);
            }
        }
    }

    public function hookNodeFormData(
        &$content,
        $type
    ) {
        if ($type === 'article') {
            if (!empty($content[ 'image' ])) {
                return;
            }

            $content['image'] = $this->config->get('settings.new_default_image', '');
        }
    }
}
