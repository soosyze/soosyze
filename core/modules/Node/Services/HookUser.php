<?php

namespace Node\Services;

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
            'node.index'              => 'Accéder à la page de vue d\'ensemble du contenu',
            'node.administer'         => 'Outrepasser le contrôle d\'accès au contenu',
            'node.show.published'     => 'Voir le contenu publié',
            'node.show.not_published' => 'Voir le contenu non publié',
        ];
        foreach ($node_types as $node_type) {
            $permission[ 'Node' ] += [
                'node.show.' . $node_type[ 'node_type' ]    => '<i>' . $node_type[ 'node_type_name' ] . '</i> : Voir le contenu',
                'node.created.' . $node_type[ 'node_type' ] => '<i>' . $node_type[ 'node_type_name' ] . '</i> : Créer un nouveau contenu',
                'node.edited.' . $node_type[ 'node_type' ]  => '<i>' . $node_type[ 'node_type_name' ] . '</i> : Modifier n\'importe quel contenu ',
                'node.deleted.' . $node_type[ 'node_type' ] => '<i>' . $node_type[ 'node_type_name' ] . '</i> : Supprimer n\'importe quel contenu'
            ];
        }
    }

    public function hookRouteNodeSow($id)
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
    
    public function hookRouteNodeAdd($user)
    {
        return !empty($user);
    }

    public function hookRouteNodeCreated($type)
    {
        return [ 'node.administer', 'node.created.' . $type ];
    }

    public function hookRouteNodeEdited($id)
    {
        $node = $this->query->from('node')
            ->where('id', '==', $id)
            ->fetch();

        return [ 'node.administer', 'node.edited.' . $node[ 'type' ] ];
    }

    public function hookRouteNodeDeleted($id)
    {
        $node = $this->query->from('node')
            ->where('id', '==', $id)
            ->fetch();

        return $node
            ? [ 'node.administer', 'node.deleted.' . $node[ 'type' ] ]
            : '';
    }
}
