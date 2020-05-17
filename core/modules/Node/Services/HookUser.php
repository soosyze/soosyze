<?php

namespace SoosyzeCore\Node\Services;

class HookUser
{
    /**
     * @var \Queryflatfile\Request
     */
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function hookPermission(&$permission)
    {
        $nodeTypes = $this->query->from('node_type')->fetchAll();

        $permission[ 'Node' ] = [
            'node.index'              => t('Go to the content overview page'),
            'node.administer'         => t('Override access control to content'),
            'node.show.published'     => t('View published content'),
            'node.show.not_published' => t('View unpublished content'),
        ];

        foreach ($nodeTypes as $nodeType) {
            $permission[ 'Node ' . $nodeType[ 'node_type_name' ] ] = [
                'node.show.published.' . $nodeType[ 'node_type' ]      => '<i>' . $nodeType[ 'node_type_name' ] . '</i> : ' . t('View published content'),
                'node.show.not_published.' . $nodeType[ 'node_type' ] => '<i>' . $nodeType[ 'node_type_name' ] . '</i> : ' . t('View unpublished content'),
                'node.created.' . $nodeType[ 'node_type' ]            => '<i>' . $nodeType[ 'node_type_name' ] . '</i> : ' . t('Create new content'),
                'node.cloned.' . $nodeType[ 'node_type' ]             => '<i>' . $nodeType[ 'node_type_name' ] . '</i> : ' . t('Clone any content'),
                'node.edited.' . $nodeType[ 'node_type' ]             => '<i>' . $nodeType[ 'node_type_name' ] . '</i> : ' . t('Edit any content'),
                'node.deleted.' . $nodeType[ 'node_type' ]            => '<i>' . $nodeType[ 'node_type_name' ] . '</i> : ' . t('Delete any content')
            ];
        }
    }

    public function hookNodeClone($idNode)
    {
        $node = $this->query
            ->from('node')
            ->where('id', '==', $idNode)
            ->fetch();

        return $node
            ? [ 'node.administer', 'node.cloned.' . $node[ 'type' ] ]
            : '';
    }

    public function hookNodeSow($idNode)
    {
        $node = $this->query
            ->from('node')
            ->where('id', '==', $idNode)
            ->fetch();

        return $node
            ? [
                'node.administer',
                $node[ 'node_status_id' ] !== 1
                    ? 'node.show.not_published'
                    : 'node.show.published',
                $node[ 'node_status_id' ] !== 1
                    ? 'node.show.not_published.' . $node[ 'type' ]
                    : 'node.show.published' . $node[ 'type' ]
            ]
            : '';
    }

    public function hookNodeAdd($req, $user)
    {
        return !empty($user);
    }

    public function hookNodeCreated($type)
    {
        return [ 'node.administer', 'node.created.' . $type ];
    }

    public function hookNodeEdited($idNode)
    {
        $node = $this->query
            ->from('node')
            ->where('id', '==', $idNode)
            ->fetch();

        return $node
            ? [ 'node.administer', 'node.edited.' . $node[ 'type' ] ]
            : '';
    }

    public function hookNodeDeleted($idNode)
    {
        $node = $this->query
            ->from('node')
            ->where('id', '==', $idNode)
            ->fetch();

        return $node
            ? [ 'node.administer', 'node.deleted.' . $node[ 'type' ] ]
            : '';
    }
}
