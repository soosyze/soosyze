<?php

namespace SoosyzeCore\Node\Services;

class Node
{
    protected $query;

    /**
     * @var \SoosyzeCore\Template\Services\Templating
     */
    protected $tpl;

    protected $core;

    public function __construct($query, $tpl)
    {
        $this->query     = $query;
        $this->tpl       = $tpl;
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function makeFieldsByData($type, array $data)
    {
        $out = [];
        foreach ($data as $value) {
            $out[] = $this->makeFieldsById($type, $value[ 'node_id' ]);
        }

        return $out;
    }

    public function makeFieldsById($entity, $id_entity)
    {
        $data         = $this->getEntity($entity, $id_entity);
        $data[ 'id' ] = $id_entity;

        return $this->makeFields($this->getFields($entity), $data);
    }

    public function makeFieldsByEntity($entity, $data, $options)
    {
        $query = $this->query
            ->from('entity_' . $entity)
            ->where($options[ 'foreign_key' ], '==', $data[ $options[ 'foreign_key' ] ]);
        if (isset($options[ 'order_by' ])) {
            $query->orderBy($options[ 'order_by' ], $options[ 'sort' ]);
        }
        $data = $query->fetchAll();

        $type = $this->getFields($entity);
        $out  = [];
        foreach ($data as $value) {
            $out[] = $this->makeFields($type, $value);
        }

        return $out;
    }

    public function getFields($type)
    {
        return $this->query
                ->select('field_name', 'field_type', 'field_label', 'field_show_label', 'field_option', 'field_weight')
                ->from('node_type_field')
                ->leftJoin('field', 'field_id', 'field.field_id')
                ->where('node_type', $type)
                ->where('field_show', true)
                ->orderby('field_weight')
                ->fetchAll();
    }

    public function makeFields(array $fields, array $data)
    {
        $out = [];
        foreach ($fields as $value) {
            $key = $value[ 'field_name' ];
            if (isset($data[ $key ])) {
                $value[ 'field_display' ] = '<div>' . $data[ $key ] . '</div>';
                $value[ 'field_value' ]   = $data[ $key ];
            }
            $out[ $key ] = $value;
            if ($value[ 'field_type' ] === 'image') {
                $out[ $key ][ 'field_display' ] = '<img src="' . $data[ $key ] . '">';
            } elseif ($value[ 'field_type' ] === 'file') {
                $out[ $key ][ 'field_display' ] = '<a href="' . $data[ $key ] . '">' . $data[ $key ] . '</a>';
            } elseif ($value[ 'field_type' ] === 'select') {
                $options                        = json_decode($value[ 'field_option' ], true);
                $out[ $key ][ 'field_display' ] = '<p>' . $options[ $data[ $key ] ] . '</p>';
            } elseif ($value[ 'field_type' ] === 'radio') {
                $options                        = json_decode($value[ 'field_option' ], true);
                $out[ $key ][ 'field_display' ] = '<p>' . $options[ $data[ $key ] ] . '</p>';
            } elseif ($value[ 'field_type' ] === 'checkbox') {
                $options                        = json_decode($value[ 'field_option' ], true);
                $explode                        = explode(',', $data[ $key ]);
                $intersect                      = array_intersect_key($options, array_flip($explode));
                $out[ $key ][ 'field_display' ] = '<p>' . implode(', ', $intersect) . '</p>';
            } elseif ($value[ 'field_type' ] === 'one_to_many') {
                $option = json_decode($value[ 'field_option' ], true);

                $out[ $key ][ 'field_value' ]   = '';
                $out[ $key ][ 'field_display' ] = $this->tpl
                    ->createBlock('entity-show.php', $this->pathViews)
                    ->addVars([
                        'entities' => $this->makeFieldsByEntity($key, $data, $option)
                    ])
                    ->addNamesOverride([ 'entity_' . $value[ 'field_name' ] . '_show' ]);
            }
        }

        return $out;
    }

    public function byId($id_node)
    {
        return $this->query
                ->from('node')
                ->where('id', '==', $id_node)
                ->fetch();
    }

    public function getEntity($entity, $id_entity)
    {
        return $this->query
                ->from('entity_' . $entity)
                ->where($entity . '_id', '==', $id_entity)
                ->fetch();
    }

    public function getFieldRelationByEntity($entity)
    {
        return $this->query
                ->from('node_type_field')
                ->leftJoin('field', 'field_id', 'field.field_id')
                ->where('field_name', $entity)
                ->where('field_type', 'one_to_many')
                ->fetch();
    }

    public function getFieldsForm($type)
    {
        return $this->query
                ->from('node_type')
                ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
                ->leftJoin('field', 'field_id', 'field.field_id')
                ->where('field_show_form', true)
                ->where('node_type', $type)
                ->fetchAll();
    }

    public function getFieldsDisplay($type)
    {
        return $this->query
                ->from('node_type')
                ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
                ->leftJoin('field', 'field_id', 'field.field_id')
                ->where('node_type', $type)
                ->where('field_show_form', true)
                ->orderBy('field_weight')
                ->fetchAll();
    }

    public function getFieldsEntity($entity)
    {
        return $this->query
                ->from('node_type_field')
                ->leftJoin('field', 'field_id', 'field.field_id')
                ->where('node_type', $entity)
                ->where('field_show_form', true)
                ->orderBy('field_weight', 'asc')
                ->fetchAll();
    }

    public function isMaxEntity($entity, $foreign_key, $id_node, $count)
    {
        if ($count === 0) {
            return false;
        }
        $data = $this->query
            ->from('entity_' . $entity)
            ->where($foreign_key, $id_node)
            ->limit($count + 1)
            ->fetchAll();

        return count($data) >= $count;
    }

    public function getRules($field)
    {
        $out = [];
        if (preg_match('/^(.*\|)?required(\|.*)?/', $field[ 'field_rules' ])) {
            $out[ 'required' ] = 1;
        }
        if (preg_match('/[\|]?(max|max_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $field[ 'field_rules' ], $matches)) {
            $out[ 'max' ] = (int) $matches[ 2 ];
        }
        if (preg_match('/[\|]?(min|min_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $field[ 'field_rules' ], $matches)) {
            $out[ 'min' ] = (int) $matches[ 2 ];
        }

        return $out;
    }
}
