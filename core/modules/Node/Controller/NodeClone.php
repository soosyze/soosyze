<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Http\Redirect;

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
        $this->oldIdNode = $idNode;
        $this->node      = self::node()->byId($idNode);
        if (!$this->node) {
            return $this->get404($req);
        }
        $this->entityNode    = self::node()->getEntity($this->node[ 'type' ], $this->node[ 'entity_id' ]);
        $this->oldEntityNode = $this->entityNode;
        if (!$this->entityNode) {
            return $this->get404($req);
        }
        if (!($fields = self::node()->getFieldsForm($this->node[ 'type' ]))) {
            return $this->get404($req);
        }
        if (mb_strlen($this->node[ 'title' ] . ' clone') > 255) {
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'Clone content title is too long' ];

            return new Redirect(self::router()->getRoute('node.admin'));
        }

        /* Construit l'entity principale */
        $this->createNewEntityNode();

        /* Construit la node */
        $this->createNewNode();

        /* Copie le répertoire de fichiers */
        $source = self::core()->getDir('files_public') . '/node/' . $this->node[ 'type' ] . '/' . $idNode;
        $dist   = self::core()->getDir('files_public') . '/node/' . $this->node[ 'type' ] . '/' . $this->node[ 'id' ];

        $this->duplicateFile($source, $dist);

        /* Parcours les champs de l'entité principal. */
        foreach ($fields as $value) {
            $fieldName = $value[ 'field_name' ];

            if (in_array($value[ 'field_type' ], [ 'text', 'textarea' ])) {
                $this->replaceLink($fieldName, $this->entityNode);
            } elseif (in_array($value[ 'field_type' ], [ 'file', 'image' ])) {
                /* Copie ses fichiers. */
                $this->replaceFileLink($fieldName, $this->entityNode);
            } elseif (in_array($value[ 'field_type' ], [ 'one_to_many' ])) {
                /* Si elle possède des sous entités. */
                $this->duplicateEntity($fieldName, json_decode($value[ 'field_option' ], true));
            }
        }
        self::query()
            ->update('entity_' . $this->node[ 'type' ], $this->entityNode)
            ->where($this->node[ 'type' ] . '_id', '=', $this->node[ 'entity_id' ])
            ->execute();

        return new Redirect(
            self::router()->getRoute('node.edit', [
                ':id_node' => $this->node[ 'id' ]
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

    private function duplicateEntity(string $fieldName, array $options): void
    {
        $relationTable = $options[ 'relation_table' ];
        $foreignKey    = $options[ 'foreign_key' ];

        $entities = self::query()
            ->from($relationTable)
            ->where($foreignKey, '=', $this->oldEntityNode[ $foreignKey ])
            ->fetchAll();

        $fields = self::node()->getFieldsEntity($fieldName);

        /* Parcours toutes les sous entités. */
        foreach ($entities as $entity) {
            foreach ($fields as $value) {
                if (in_array($value[ 'field_type' ], [ 'text', 'textarea' ])) {
                    $this->replaceLink($value[ 'field_name' ], $entity);
                } elseif (in_array($value[ 'field_type' ], [ 'file', 'image' ])) {
                    $this->replaceFileLink($value[ 'field_name' ], $entity);
                }
            }
            /* Supprime l'identifiant copié. */
            unset($entity[ $fieldName . '_id' ]);
            /* Copie l'identifiant de l'entité principal pour la sous node. */
            $entity[ $foreignKey ] = $this->entityNode[ $foreignKey ];
            self::query()
                ->insertInto($relationTable, array_keys($entity))
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

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($targetDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy((string) $item, $targetDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    private function replaceLink(string $fieldName, array &$entity): string
    {
        $linkSource[] = 'node/' . $this->oldIdNode;
        $linkSource[] = self::alias()->getAlias('node/' . $this->oldIdNode, 'node/' . $this->oldIdNode);

        $linkTarget = 'node/' . $this->node[ 'id' ];

        /* Remplace les liens de la node courant par la node clonée. */
        $entity[ $fieldName ] = str_replace($linkSource, $linkTarget, $entity[ $fieldName ]);

        $this->replaceFileLink($fieldName, $entity);

        return $entity[ $fieldName ];
    }

    private function replaceFileLink(string $fieldName, array &$entity): void
    {
        $entity[ $fieldName ] = str_replace(
            'node/' . $this->node[ 'type' ] . '/' . $this->oldIdNode,
            'node/' . $this->node[ 'type' ] . '/' . $this->node[ 'id' ],
            $entity[ $fieldName ]
        );
    }

    private static function getBasename(string $pathFile): string
    {
        return strtolower(pathinfo($pathFile, PATHINFO_BASENAME));
    }
}
