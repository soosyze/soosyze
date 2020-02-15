<?php

namespace SoosyzeCore\Node\Services;

class Node
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function makeFieldsByData($type, array $data)
    {
        $out = [];
        foreach ($data as $value) {
            $out[] = $this->makeFieldsById($type, $value[ 'node_id' ]);
        }

        return $out;
    }

    public function makeFieldsById($type, $id)
    {
        $data         = $this->query
            ->from('entity_' . $type)
            ->where($type . '_id', '==', $id)
            ->fetch();
        $data[ 'id' ] = $id;

        return $this->makeFields($this->getFields($type), $data);
    }

    public function getFields($type)
    {
        return $this->query
                ->select('field_name', 'field_type', 'field_label', 'field_option', 'field_weight')
                ->from('node_type_field')
                ->leftJoin('field', 'field_id', 'field.field_id')
                ->where('node_type', $type)
                ->orderby('field_weight')
                ->fetchAll();
    }

    public function makeFields(array $fields, array $data)
    {
        $out = [];
        foreach ($fields as $value) {
            $key                      = $value[ 'field_name' ];
            $value[ 'field_display' ] = '<div>' . $data[ $key ] . '</div>';
            $value[ 'field_value' ]   = $data[ $key ];
            $out[ $key ]              = $value;
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
            }
        }

        return $out;
    }
}
