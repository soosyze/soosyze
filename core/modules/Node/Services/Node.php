<?php

namespace SoosyzeCore\Node\Services;

class Node
{
    protected $config;

    protected $core;

    protected $pathViews;

    protected $query;

    /**
     * @var \SoosyzeCore\Template\Services\Templating
     */
    protected $tpl;

    protected $nodeCurrent = null;

    public function __construct($config, $core, $query, $tpl)
    {
        $this->config = $config;
        $this->core   = $core;
        $this->query  = $query;
        $this->tpl    = $tpl;

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

    public function makeFieldsById($entity, $idEntity)
    {
        $data         = $this->getEntity($entity, $idEntity);
        $data[ 'id' ] = $idEntity;

        return $this->makeFields($entity, $this->getFields($entity), $data);
    }

    public function makeFieldsByEntity($entity, array $data, array $options)
    {
        $this->query
            ->from('entity_' . $entity)
            ->where($options[ 'foreign_key' ], '==', $data[ $options[ 'foreign_key' ] ]);

        if (isset($options[ 'order_by' ])) {
            $this->query->orderBy($options[ 'order_by' ], $options[ 'sort' ]);
        }

        $data   = $this->query->fetchAll();
        $fields = $this->getFields($entity);
        $out    = [];

        foreach ($data as $value) {
            $out[] = $this->makeFields($entity, $fields, $value);
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

    public function getCurrentNode($idNode = null)
    {
        if (!$this->nodeCurrent && $idNode !== null) {
            $this->nodeCurrent = $this->byId($idNode);
        }

        return $this->nodeCurrent;
    }

    public function byId($idNode)
    {
        return $this->query
                ->from('node')
                ->where('id', '==', $idNode)
                ->fetch();
    }

    public function getEntity($entity, $idEntity)
    {
        return $this->query
                ->from('entity_' . $entity)
                ->where($entity . '_id', '==', $idEntity)
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
                ->orderBy('field_weight')
                ->fetchAll();
    }

    public function deleteRelation($node)
    {
        /* Suppression des relations */
        $entity = $this->getEntity($node[ 'type' ], $node[ 'entity_id' ]);

        $relationNode = $this->query
            ->from('node_type_field')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $node[ 'type' ])
            ->where('field_type', 'one_to_many')
            ->fetchAll();
        foreach ($relationNode as $relation) {
            $options = json_decode($relation[ 'field_option' ], true);
            $this->query
                ->from($options[ 'relation_table' ])
                ->delete()
                ->where($options[ 'foreign_key' ], $entity[ $options[ 'local_key' ] ])
                ->execute();
        }

        /* Supression du contenu. */
        $this->query
            ->from('entity_' . $node[ 'type' ])
            ->delete()
            ->where($node[ 'type' ] . '_id', '==', $node[ 'entity_id' ])
            ->execute();
    }

    public function deleteFile($type, $idNode)
    {
        $dir = $this->core->getSettingEnv('files_public', 'app/files') . "/node/$type/$idNode";
        if (!is_dir($dir)) {
            return;
        }

        $dirIterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $iterator    = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::CHILD_FIRST);

        /* Supprime tous les dossiers et fichiers */
        foreach ($iterator as $file) {
            $file->isDir()
                    ? \rmdir($file)
                    : \unlink($file);
        }
        /* Supprime le dossier cible. */
        \rmdir($dir);
    }

    public function isMaxEntity($entity, $foreignKey, $idNode, $count)
    {
        if ($count === 0) {
            return false;
        }
        $data = $this->query
            ->from('entity_' . $entity)
            ->where($foreignKey, $idNode)
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
        if (preg_match('/[\|]?(between|between_numeric):(\d+),(\d+)?/', $field[ 'field_rules' ], $matches)) {
            $out[ 'min' ] = (int) $matches[ 2 ];
            $out[ 'max' ] = (int) $matches[ 3 ];
        }
        if (preg_match('/[\|]?(max|max_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $field[ 'field_rules' ], $matches)) {
            $out[ 'max' ] = (int) $matches[ 2 ];
        }
        if (preg_match('/[\|]?(min|min_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $field[ 'field_rules' ], $matches)) {
            $out[ 'min' ] = (int) $matches[ 2 ];
        }

        return $out;
    }

    public function getPathSettings()
    {
        return [
            [
                'key'      => 'settings.path_index',
                'path'     => $this->config->get('settings.path_index'),
                'title'    => 'Default homepage',
                'required' => 1
            ],
            [
                'key'   => 'settings.path_no_found',
                'path'  => $this->config->get('settings.path_no_found'),
                'title' => 'Page 404 by default (page not found)'
            ],
            [
                'key'   => 'settings.path_access_denied',
                'path'  => $this->config->get('settings.path_access_denied'),
                'title' => 'Page 403 by default (access denied)'
            ],
            [
                'key'   => 'settings.path_maintenance',
                'path'  => $this->config->get('settings.path_maintenance'),
                'title' => 'Default maintenance page'
            ],
            [
                'key'   => 'settings.connect_redirect',
                'path'  => $this->config->get('settings.connect_redirect'),
                'title' => 'Redirect page after connection'
            ],
            [
                'key'   => 'settings.rgpd_page',
                'path'  => $this->config->get('settings.rgpd_page'),
                'title' => 'GDPR Page'
            ],
            [
                'key'   => 'settings.terms_of_service_page',
                'path'  => $this->config->get('settings.terms_of_service_page'),
                'title' => 'Terms page'
            ]
        ];
    }

    private function makeFields($type, array $fields, array $data)
    {
        $out = [];

        $this->core->callHook('node.makefields', [ $type, &$fields, &$data ]);

        foreach ($fields as $value) {
            $key = $value[ 'field_name' ];

            $out[ $key ] = $value;

            if (isset($data[ $key ])) {
                $out[ $key ][ 'field_value' ]   = $data[ $key ];
                $out[ $key ][ 'field_display' ] = '<div>' . $data[ $key ] . '</div>';
            }
            if ($value[ 'field_type' ] === 'image') {
                $link = is_file($data[ $key ])
                    ? $this->core->getRequest()->getBasePath() . $data[ $key ]
                    : $data[ $key ];

                $out[ $key ][ 'field_value' ]   = $link;
                $out[ $key ][ 'field_display' ] = '<img src="' . $link . '" alt="' . $value[ 'field_label' ] . '">';
            } elseif ($value[ 'field_type' ] === 'file') {
                $link = is_file($data[ $key ])
                    ? $this->core->getRequest()->getBasePath() . $data[ $key ]
                    : $data[ $key ];

                $out[ $key ][ 'field_value' ]   = $link;
                $out[ $key ][ 'field_display' ] = '<a href="' . $link . '">' . $data[ $key ] . '</a>';
            } elseif ($value[ 'field_type' ] === 'select') {
                $options = json_decode($value[ 'field_option' ], true);

                $out[ $key ][ 'field_display' ] = '<p>' . $options[ $data[ $key ] ] . '</p>';
            } elseif ($value[ 'field_type' ] === 'radio') {
                $options = json_decode($value[ 'field_option' ], true);

                $out[ $key ][ 'field_display' ] = '<p>' . $options[ $data[ $key ] ] . '</p>';
            } elseif ($value[ 'field_type' ] === 'checkbox') {
                $options   = json_decode($value[ 'field_option' ], true);
                $explode   = explode(',', $data[ $key ]);
                $intersect = array_intersect_key($options, array_flip($explode));

                $out[ $key ][ 'field_display' ] = '<p>' . implode(', ', $intersect) . '</p>';
            } elseif ($value[ 'field_type' ] === 'one_to_many') {
                $option = json_decode($value[ 'field_option' ], true);

                $out[ $key ][ 'field_value' ]   = $this->makeFieldsByEntity($key, $data, $option);
                $out[ $key ][ 'field_display' ] = $this->tpl
                    ->getTheme()
                    ->createBlock('node/content-entity-show.php', $this->pathViews)
                    ->addVars([
                        'entities' => $out[ $key ][ 'field_value' ]
                    ])
                    ->addNamesOverride([ 'node/content-entity_' . $value[ 'field_name' ] . '-show.php' ]);

                $this->core->callHook('node.entity.' . $value[ 'field_name' ] . '.show', [
                    &$out[ $key ][ 'field_display' ]
                ]);
            }
        }

        return $out;
    }
}
