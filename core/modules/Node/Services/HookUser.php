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
        $node_types           = $this->query->from('node_type')->fetchAll();
        $permission[ 'Node' ] = [
            'node.index'              => t('Go to the content overview page'),
            'node.administer'         => t('Override access control to content'),
            'node.show.published'     => t('View published content'),
            'node.show.not_published' => t('View unpublished content'),
        ];
        foreach ($node_types as $node_type) {
            $permission[ 'Node' ] += [
                'node.show.' . $node_type[ 'node_type' ]    => '<i>' . $node_type[ 'node_type_name' ] . '</i> : ' . t('View content'),
                'node.created.' . $node_type[ 'node_type' ] => '<i>' . $node_type[ 'node_type_name' ] . '</i> : ' . t('Create new content'),
                'node.edited.' . $node_type[ 'node_type' ]  => '<i>' . $node_type[ 'node_type_name' ] . '</i> : ' . t('Edit any content'),
                'node.deleted.' . $node_type[ 'node_type' ] => '<i>' . $node_type[ 'node_type_name' ] . '</i> : ' . t('Delete any content')
            ];
        }
    }

    public function hookNodeSow($id)
    {
        $node = $this->query->from('node')
            ->where('id', '==', $id)
            ->fetch();

        return $node
            ? [ !$node[ 'published' ]
                    ? 'node.show.not_published'
                    : 'node.show.published',
                'node.show.' . $node[ 'type' ],
                'node.administer' ]
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

    public function hookNodeEdited($id)
    {
        $node = $this->query->from('node')
            ->where('id', '==', $id)
            ->fetch();

        return [ 'node.administer', 'node.edited.' . $node[ 'type' ] ];
    }

    public function hookNodeDeleted($id)
    {
        $node = $this->query->from('node')
            ->where('id', '==', $id)
            ->fetch();

        return $node
            ? [ 'node.administer', 'node.deleted.' . $node[ 'type' ] ]
            : '';
    }
}
