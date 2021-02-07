<?php

namespace SoosyzeCore\Node\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    /**
     * @var \SoosyzeCore\QueryBuilder\Services\Query
     */
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function hookUserPermissionModule(array &$permissions)
    {
        $nodeTypes = $this->query->from('node_type')->fetchAll();

        $permissions[ 'Node' ] = [
            'node.administer'         => 'Override access control to content',
            'node.manager'            => 'Go to the content overview page',
            'node.show.published'     => 'View any published content',
            'node.show.not_published' => 'View any unpublished content',
            'node.show.own'           => 'View own published and unpublished content',
            'node.user.edit'          => 'Edit user',
            'node.cloned.own'         => 'Clone own content',
            'node.edited.own'         => 'Edit own content',
            'node.deleted.own'        => 'Delete own content'
        ];

        foreach ($nodeTypes as $nodeType) {
            $permissions[ 'Node ' . $nodeType[ 'node_type_name' ] ] = [
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

    public function hookNodeSow($idNode, $req, $user)
    {
        $node   = $this->getNode($idNode);
        $rights = '';

        if ($node) {
            $rights = [ 'node.administer' ];

            if ($user && $user[ 'user_id' ] == $node[ 'user_id' ]) {
                $rights[] = 'node.show.own';
            }

            if ($node[ 'node_status_id' ] !== 1) {
                $rights[] = 'node.show.not_published';
                $rights[] = 'node.show.not_published.' . $node[ 'type' ];
            } else {
                $rights[] = 'node.show.published';
                $rights[] = 'node.show.published.' . $node[ 'type' ];
            }
        }

        return $rights;
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

    public function hookNodeClone($idNode, $req, $user)
    {
        $node   = $this->getNode($idNode);
        $rights = '';

        if ($node) {
            $rights = [ 'node.administer', 'node.cloned.' . $node[ 'type' ] ];

            if ($user && $user[ 'user_id' ] == $node[ 'user_id' ]) {
                $rights[] = 'node.cloned.own';
            }
        }

        return $rights;
    }

    public function hookNodeEdited($idNode, $req, $user)
    {
        $node   = $this->getNode($idNode);
        $rights = '';

        if ($node) {
            $rights = [ 'node.administer', 'node.edited.' . $node[ 'type' ] ];

            if ($user && $user[ 'user_id' ] == $node[ 'user_id' ]) {
                $rights[] = 'node.edited.own';
            }
        }

        return $rights;
    }

    public function hookNodeDeleted($idNode, $req, $user)
    {
        $node   = $this->getNode($idNode);
        $rights = '';

        if ($node) {
            $rights = [ 'node.administer', 'node.deleted.' . $node[ 'type' ] ];

            if ($user && $user[ 'user_id' ] == $node[ 'user_id' ]) {
                $rights[] = 'node.deleted.own';
            }
        }

        return $rights;
    }

    public function hookEntityCreated($idNode, $entity, $req, $user)
    {
        $node = $this->getNode($idNode);

        return $this->hookNodeCreated($node[ 'type' ]);
    }

    public function hookEntityEdited($idNode, $entity, $idEntity, $req, $user)
    {
        return $this->hookNodeEdited($idNode, $req, $user);
    }

    public function hookEntityDeleted($idNode, $entity, $idEntity, $req, $user)
    {
        return $this->hookNodeDeleted($idNode, $req, $user);
    }

    private function getNode($idNode)
    {
        if (isset($this->nodes[ $idNode ])) {
            return $this->nodes[ $idNode ];
        }

        $this->nodes[ $idNode ] = $this->query
            ->from('node')
            ->where('id', '==', $idNode)
            ->fetch();

        return $this->nodes[ $idNode ];
    }
}
