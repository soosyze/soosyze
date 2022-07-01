<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Node\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Http\Redirect;
use Soosyze\Core\Modules\Node\Model\Field\OneToManyOption;

/**
 * @method \Soosyze\Core\Modules\System\Services\Alias        alias()
 * @method \Soosyze\Core\Modules\Node\Services\Node           node()
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query  query()
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Schema schema()
 * @method \Soosyze\Core\Modules\User\Services\User           user()
 *
 * @phpstan-import-type NodeTypeFieldOneFieldEntity from \Soosyze\Core\Modules\Node\Extend
 */
class NodeClone extends \Soosyze\Controller
{
    /**
     * @var int
     */
    private $oldIdNode;

    /**
     * Informations de la node.
     *
     * @var array
     */
    private $node;

    /**
     * Données de la node.
     *
     * @var array
     */
    private $entityNode;

    /**
     * Données de la node avant le clone
     *
     * @var array
     */
    private $oldEntityNode;

    public function duplicate(int $idNode, ServerRequestInterface $req): ResponseInterface
    {
        $node = self::node()->byId($idNode);
        if ($node === null) {
            return $this->get404($req);
        }

        $entityNode = self::node()->getEntity($node[ 'type' ], $node[ 'entity_id' ]);
        if ($entityNode === null) {
            return $this->get404($req);
        }

        $this->oldIdNode     = $idNode;
        $this->node          = $node;
        $this->entityNode    = $entityNode;
        $this->oldEntityNode = $entityNode;

        if (!($fields = self::node()->getFieldsForm($this->node[ 'type' ]))) {
            return $this->get404($req);
        }
        if (mb_strlen($this->node[ 'title' ] . ' clone') > 255) {
            $_SESSION[ 'messages' ][ 'errors' ][] = t('Clone content title is too long');

            return new Redirect(self::router()->generateUrl('node.admin'));
        }

        /* Construit l'entity principale */
        $this->createNewEntityNode();

        /* Construit la node */
        $this->createNewNode();

        /* Copie le répertoire de fichiers */
        /** @phpstan-var string $source */
        $source = self::core()->getDir('files_public') . '/node/' . $this->node[ 'type' ] . '/' . $idNode;
        /** @phpstan-var string $dist */
        $dist   = self::core()->getDir('files_public') . '/node/' . $this->node[ 'type' ] . '/' . $this->node[ 'id' ];

        $this->duplicateFile($source, $dist);

        /* Parcours les champs de l'entité principal. */
        foreach ($fields as $value) {
            $fieldName = $value[ 'field_name' ];
            $entityData = $this->entityNode[$fieldName];

            if (in_array($value[ 'field_type' ], [ 'text', 'textarea' ]) && is_string($entityData)) {
                $this->entityNode[$fieldName] = $this->replaceLink($entityData);
            } elseif (in_array($value[ 'field_type' ], [ 'file', 'image' ]) && is_string($entityData)) {
                /* Copie ses fichiers. */
                $this->entityNode[$fieldName] = $this->replaceFileLink($entityData);
            } elseif ($value[ 'field_type' ] == 'one_to_many') {
                $oneToManyOption = OneToManyOption::createFromJson($value[ 'field_option' ]);

                /* Si elle possède des sous entités. */
                $this->duplicateEntity($fieldName, $oneToManyOption);
            }
        }
        self::query()
            ->update('entity_' . $this->node[ 'type' ], $this->entityNode)
            ->where($this->node[ 'type' ] . '_id', '=', $this->node[ 'entity_id' ])
            ->execute();

        return new Redirect(
            self::router()->generateUrl('node.edit', [
                'idNode' => $this->node[ 'id' ]
            ]),
            302
        );
    }

    private function createNewNode(): void
    {
        unset($this->node[ 'id' ], $this->node[ 'node_status_id' ]);
        $this->node[ 'date_created' ] = (string) time();
        $this->node[ 'date_changed' ] = (string) time();
        $this->node[ 'entity_id' ]    = $this->entityNode[ $this->node[ 'type' ] . '_id' ];
        $this->node[ 'title' ]        .= ' clone';
        $this->node[ 'user_id' ]      = self::user()->isConnected()[ 'user_id' ] ?? null;

        self::query()
            ->insertInto('node', array_keys($this->node))
            ->values($this->node)
            ->execute();

        $this->node[ 'id' ] = self::schema()->getIncrement('node');
    }

    private function createNewEntityNode(): void
    {
        unset($this->entityNode[ $this->node[ 'type' ] . '_id' ]);
        self::query()
            ->insertInto('entity_' . $this->node[ 'type' ], array_keys($this->entityNode))
            ->values($this->entityNode)
            ->execute();

        $this->entityNode[ $this->node[ 'type' ] . '_id' ] = self::schema()->getIncrement('entity_' . $this->node[ 'type' ]);
    }

    private function duplicateEntity(string $fieldName, OneToManyOption $oneToManyOption): void
    {
        $foreignKey = $oneToManyOption->getForeignKey();

        $entities = self::query()
            ->from($oneToManyOption->getRelationTable())
            ->where($foreignKey, '=', $this->oldEntityNode[ $foreignKey ])
            ->fetchAll();

        /** @phpstan-var array<NodeTypeFieldOneFieldEntity> $fields */
        $fields = self::node()->getFieldsEntity($fieldName);

        /* Parcours toutes les sous entités. */
        foreach ($entities as $entity) {
            foreach ($fields as $value) {
                $entityData = $entity[ $value[ 'field_name' ] ];

                if (in_array($value[ 'field_type' ], [ 'text', 'textarea' ]) && is_string($entityData)) {
                    $entity[ $value[ 'field_name' ] ] = $this->replaceLink($entityData);
                } elseif (in_array($value[ 'field_type' ], [ 'file', 'image' ]) && is_string($entityData)) {
                    $entity[ $value[ 'field_name' ] ] = $this->replaceFileLink($entityData);
                }
            }
            /* Supprime l'identifiant copié. */
            unset($entity[ $fieldName . '_id' ]);
            /* Copie l'identifiant de l'entité principal pour la sous node. */
            $entity[ $foreignKey ] = $this->entityNode[ $foreignKey ];
            self::query()
                ->insertInto($oneToManyOption->getRelationTable(), array_keys($entity))
                ->values($entity)
                ->execute();
        }
    }

    private function duplicateFile(string $sourceDir, string $targetDir): void
    {
        if (!file_exists($sourceDir)) {
            return;
        }
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $recursiveDirectory = new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator           = new \RecursiveIteratorIterator($recursiveDirectory, \RecursiveIteratorIterator::SELF_FIRST);

        /** @phpstan-var \SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($targetDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy((string) $item, $targetDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    private function replaceLink(string $str): string
    {
        $linkSource[] = 'node/' . $this->oldIdNode;
        $linkSource[] = self::alias()->getAlias('node/' . $this->oldIdNode, 'node/' . $this->oldIdNode);

        $linkTarget = 'node/' . $this->node[ 'id' ];

        /* Remplace les liens de la node courant par la node clonée. */
        $strReplaceLink = str_replace($linkSource, $linkTarget, $str);

        return $this->replaceFileLink($strReplaceLink);
    }

    private function replaceFileLink(string $str): string
    {
        return str_replace(
            'node/' . $this->node[ 'type' ] . '/' . $this->oldIdNode,
            'node/' . $this->node[ 'type' ] . '/' . $this->node[ 'id' ],
            $str
        );
    }
}
