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
            'node.administer'         => 'Override access control to content',
            'node.manager'            => 'Go to the content overview page',
            'node.show.published'     => 'View any published content',
            'node.show.not_published' => 'View any unpublished content',
        ];

        foreach ($nodeTypes as $nodeType) {
            $permission[ 'Node ' . $nodeType[ 'node_type_name' ] ] = [
                'node.show.published.' . $nodeType[ 'node_type' ]     => 'View published content',
                'node.show.not_published.' . $nodeType[ 'node_type' ] => 'View unpublished content',
                'node.created.' . $nodeType[ 'node_type' ]            => 'Create new content',
                'node.cloned.' . $nodeType[ 'node_type' ]             => 'Clone any content',
                'node.edited.' . $nodeType[ 'node_type' ]             => 'Edit any content',
                'node.deleted.' . $nodeType[ 'node_type' ]            => 'Delete any content'
            ];
        }
    }

    public function hookNodeManager()
    {
        return [ 'node.administer', 'node.manager' ];
    }

    public function hookNodeClone($idNode)
    {
        $node = $this->getNode($idNode);

        return $node
            ? [ 'node.administer', 'node.cloned.' . $node[ 'type' ] ]
            : '';
    }

    public function hookNodeSow($idNode)
    {
        $node = $this->getNode($idNode);
        
        return $node
            ? [
                'node.administer',
                $node[ 'node_status_id' ] !== 1
                    ? 'node.show.not_published'
                    : 'node.show.published',
                $node[ 'node_status_id' ] !== 1
                    ? 'node.show.not_published.' . $node[ 'type' ]
                    : 'node.show.published.' . $node[ 'type' ]
            ]
            : '';
    }

    public function hookNodeAdd($req, $user)
    {
        $nodeTypes = $this->query->from('node_type')->fetchAll();
        $rights    = [ 'node.administer' ];

        foreach ($nodeTypes as $nodeType) {
            $rights[] = 'node.created.' . $nodeType[ 'node_type' ];
        }

        return $rights;
    }

    public function hookNodeCreated($type)
    {
        return [ 'node.administer', 'node.created.' . $type ];
    }

    public function hookNodeEdited($idNode)
    {
        $node = $this->getNode($idNode);

        return $node
            ? [ 'node.administer', 'node.edited.' . $node[ 'type' ] ]
            : '';
    }

    public function hookNodeDeleted($idNode)
    {
        $node = $this->getNode($idNode);

        return $node
            ? [ 'node.administer', 'node.deleted.' . $node[ 'type' ] ]
            : '';
    }
    
    public function getNode($idNode)
    {
        return $this->query
            ->from('node')
            ->where('id', '==', $idNode)
            ->fetch();
    }
}
