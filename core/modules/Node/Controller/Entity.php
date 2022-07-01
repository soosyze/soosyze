<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Node\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\Node\Form\FormNode;
use Soosyze\Core\Modules\Node\Model\Field\OneToManyOption;

/**
 * @method \Soosyze\Core\Modules\FileSystem\Services\File     file()
 * @method \Soosyze\Core\Modules\Node\Services\Node           node()
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query  query()
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Schema schema()
 * @method \Soosyze\Core\Modules\Template\Services\Templating template()
 */
class Entity extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create(int $idNode, string $entity, ServerRequestInterface $req): ResponseInterface
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        if (!($fieldNode = self::node()->getFieldRelationByEntity($entity))) {
            return $this->get404($req);
        }
        $oneToManyOption = OneToManyOption::createFromJson($fieldNode[ 'field_option' ]);

        if (!($fieldsEntity = self::node()->getFieldsEntity($entity))) {
            return $this->get404($req);
        }
        if (
            self::node()->isMaxEntity(
                $entity,
                $oneToManyOption->getForeignKey(),
                $idNode,
                $oneToManyOption->getCount()
            )
        ) {
            return $this->get404($req);
        }

        $values = [];
        $this->container->callHook('entity.create.form.data', [ &$values, $node, $entity ]);

        $form = (new FormNode([
            'action'  => self::router()->generateUrl('node.entity.store', [
                'idNode' => $idNode,
                'entity' => $entity
            ]),
            'enctype' => 'multipart/form-data',
            'method'  => 'post' ], self::file(), self::query(), self::router(), self::config()))
            ->setValues($values)
            ->setFields($fieldsEntity)
            ->entityFieldset()
            ->actionsEntitySubmit();

        $this->container->callHook('entity.create.form', [ &$form, $values, $node, $entity ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Add content of type :name', [
                        ':name' => $entity ])
                ])
                ->make('page.content', 'node/content-entity-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store(int $idNode, string $entity, ServerRequestInterface $req): ResponseInterface
    {
        if ($req->isMaxSize()) {
            return $this->json(400, [
                    'messages'    => [
                        'errors' => [
                            t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
                        ]
                    ]
            ]);
        }
        if (!($node = self::node()->byId($idNode))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        if (!($data = self::node()->getEntity($node[ 'type' ], $node[ 'entity_id' ]))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        if (!($fieldNode = self::node()->getFieldRelationByEntity($entity))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        if (!($fieldsEntity = self::node()->getFieldsEntity($entity))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        $oneToManyOption = OneToManyOption::createFromJson($fieldNode[ 'field_option' ]);
        if (
            self::node()->isMaxEntity(
                $entity,
                $oneToManyOption->getForeignKey(),
                $idNode,
                $oneToManyOption->getCount()
            )
        ) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([ 'token_entity' => 'token' ])
            ->setInputs((array) $req->getParsedBody() + $req->getUploadedFiles());

        /* Test des champs personnalisés de la node. */
        $files = [];
        foreach ($fieldsEntity as $value) {
            $key = $value[ 'field_name' ];
            $validator->addRule($key, $value[ 'field_rules' ]);
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $key;
            }
        }

        $this->container->callHook('entity.store.validator', [ &$validator, $node, $entity ]);

        if ($validator->isValid()) {
            /* Prépare les champs de la table enfant. */
            $fieldsInsert = [];
            foreach ($fieldsEntity as $value) {
                /** @phpstan-var string $key */
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $fieldsInsert[ $key ] = '';
                } elseif ($value[ 'field_type' ] === 'number') {
                    $fieldsInsert[ $key ] = $validator->getInputInt($key);
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fieldsInsert[ $key ] = implode(',', $validator->getInputArray($key));
                } else {
                    $fieldsInsert[ $key ] = $validator->getInputString($key);
                }
            }

            $fieldsInsert[ $node[ 'type' ] . '_id' ] = $data[ $node[ 'type' ] . '_id' ];

            $this->container->callHook('entity.store.before', [ $validator, &$fieldsInsert, $node, $entity ]);
            self::query()
                ->insertInto('entity_' . $entity, array_keys($fieldsInsert))
                ->values($fieldsInsert)
                ->execute();
            $this->container->callHook('entity.store.after', [ $validator, $node, $entity ]);

            /* Télécharge et enregistre les fichiers. */
            $idEntity = self::schema()->getIncrement('entity_' . $entity);
            foreach ($fieldsEntity as $value) {
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $this->saveFile($node[ 'type' ], $idNode, $entity, $idEntity, $value[ 'field_name' ], $validator);
                }
            }

            $_SESSION[ 'messages' ][ 'success' ][] = t('Your content has been saved.');

            return $this->json(201, [
                'redirect' => self::router()->generateUrl('node.edit', [ 'idNode' => $idNode ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function edit(int $idNode, string $entity, int $idEntity, ServerRequestInterface $req): ResponseInterface
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        if (!($fieldNode = self::node()->getFieldRelationByEntity($entity))) {
            return $this->get404($req);
        }
        if (!($fieldsEntity = self::node()->getFieldsEntity($entity))) {
            return $this->get404($req);
        }
        if (!($values = self::node()->getEntity($entity, $idEntity))) {
            return $this->get404($req);
        }

        $this->container->callHook('entity.edit.form.data', [
            &$values, $node, $entity, $idEntity
        ]);

        $form = (new FormNode([
            'action'  => self::router()->generateUrl('node.entity.update', [
                'idNode'   => $idNode,
                'entity'   => $entity,
                'idEntity' => $idEntity
            ]),
            'enctype' => 'multipart/form-data',
            'method'  => 'put' ], self::file(), self::query(), self::router(), self::config()))
            ->setValues($values)
            ->setFields($fieldsEntity)
            ->entityFieldset()
            ->actionsEntitySubmit();

        $this->container->callHook('entity.edit.form', [ &$form, $values, $node, $entity, $idEntity ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Edit :title content', [
                        ':title' => $entity
                    ])
                ])
                ->make('page.content', 'node/content-entity-form.php', $this->pathViews, [ 'form' => $form ]);
    }

    public function update(int $idNode, string $entity, int $idEntity, ServerRequestInterface $req): ResponseInterface
    {
        if ($req->isMaxSize()) {
            return $this->json(400, [
                    'messages'    => [
                        'errors' => [
                            t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
                        ]
                    ]
            ]);
        }

        if (!($node = self::node()->byId($idNode))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        if (!($fieldNode = self::node()->getFieldRelationByEntity($entity))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        if (!($fieldsEntity = self::node()->getFieldsEntity($entity))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        if (!self::node()->getEntity($entity, $idEntity)) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->setRules([ 'token_entity' => 'token' ])
            ->setInputs((array) $req->getParsedBody() + $req->getUploadedFiles());

        /* Test des champs personnalisé de la node. */
        $files = [];
        foreach ($fieldsEntity as $value) {
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $value[ 'field_type' ];
            }
            $validator->addRule($value[ 'field_name' ], $value[ 'field_rules' ]);
        }

        $this->container->callHook('entity.update.validator', [
            &$validator, $node, $entity, $idEntity
        ]);

        if ($validator->isValid()) {
            $fieldsUpdate = [];
            foreach ($fieldsEntity as $value) {
                /** @phpstan-var string $key */
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $this->saveFile($node['type'], $idNode, $entity, $idEntity, $key, $validator);
                } elseif ($value[ 'field_type' ] === 'number') {
                    $fieldsUpdate[ $key ] = $validator->getInputInt($key);
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fieldsUpdate[ $key ] = implode(',', $validator->getInputArray($key));
                } else {
                    $fieldsUpdate[ $key ] = $validator->getInputString($key);
                }
            }

            $this->container->callHook('entity.update.before', [
                $validator, &$fieldsUpdate, $node, $entity, $idEntity
            ]);
            self::query()
                ->update('entity_' . $entity, $fieldsUpdate)
                ->where($entity . '_id', '=', $idEntity)
                ->execute();
            $this->container->callHook('entity.update.after', [
                $validator, $node, $entity, $idEntity
            ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                'redirect' => self::router()->generateUrl('node.edit', [ 'idNode' => $idNode ])
            ]);
        }

        return $this->json(400, [
            'messages'    => [ 'errors' => $validator->getKeyErrors() ],
            'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function delete(int $idNode, string $typeEntity, int $idEntity, ServerRequestInterface $req): ResponseInterface
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        if (!($fieldNode = self::node()->getFieldRelationByEntity($typeEntity))) {
            return $this->get404($req);
        }
        if (!($fieldsEntity = self::node()->getFieldsEntity($typeEntity))) {
            return $this->get404($req);
        }
        if (!($entity = self::node()->getEntity($typeEntity, $idEntity))) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([ 'id' => 'required' ])
            ->setInputs([ 'id' => $idNode ]);

        /* Si la node est publié. */
        if ($node[ 'node_status_id' ] === 1 && ($rules = self::node()->getRules($fieldNode))) {
            $entitys = self::query()
                ->from('entity_' . $typeEntity)
                ->where($node[ 'type' ] . '_id', '=', $node[ 'entity_id' ])
                ->limit(2)
                ->fetchAll();

            /* Et que l'entité est requise, la dernière entité ne peut-être supprimé. */
            if (isset($rules[ 'required' ]) && count($entitys) === 1) {
                $validator
                    ->addRule('node_status_id', '!equal:1')
                    ->addInput('node_status_id', 1);
            }
        }

        if ($validator->isValid()) {
            self::query()
                ->from('entity_' . $typeEntity)
                ->delete()
                ->where($typeEntity . '_id', '=', $idEntity)
                ->execute();

            $this->deleteFile($fieldsEntity, $entity);

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('node.edit', [
                        'idNode'   => $idNode,
                        'entity'   => $typeEntity,
                        'idEntity' => $idEntity
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function deleteFile(array $fieldsEntity, array $entity): void
    {
        foreach ($fieldsEntity as $field) {
            if (!in_array($field[ 'field_type' ], [ 'image', 'file' ])) {
                continue;
            }

            $file = $dir = self::core()->getSetting('root', '') . $entity[ $field[ 'field_name' ] ];
            if (!is_file($file)) {
                continue;
            }
            \unlink($file);
        }
    }

    private function saveFile(
        string $typeNode,
        int $idNode,
        string $typeEntity,
        int $idEntity,
        string $nameField,
        Validator $validator
    ): void {
        /** @phpstan-var UploadedFileInterface $uploadedFile */
        $uploadedFile = $validator->getInput($nameField);

        self::file()
            ->add($uploadedFile, $validator->getInputString("file-$nameField-name"))
            ->setPath("/node/$typeNode/{$idNode}/$typeEntity")
            ->isResolvePath()
            ->isResolveName()
            ->callGet(function (string $key) use ($typeEntity, $idEntity): ?string {
                $entity = self::query()
                    ->from('entity_' . $typeEntity)
                    ->where($typeEntity . '_id', '=', $idEntity)
                    ->fetch();

                return isset($entity[$key]) && is_string($entity[ $key ])
                    ? $entity[ $key ]
                    : null;
            })
            ->callMove(function (string $key, \SplFileInfo $fileInfo) use ($typeEntity, $idEntity, $nameField): void {
                self::query()
                    ->update('entity_' . $typeEntity, [ $nameField => $fileInfo->getPathname() ])
                    ->where($typeEntity . '_id', '=', $idEntity)
                    ->execute();
            })
            ->callDelete(function () use ($typeEntity, $idEntity, $nameField): void {
                self::query()
                    ->update('entity_' . $typeEntity, [ $nameField => '' ])
                    ->where($typeEntity . '_id', '=', $idEntity)
                    ->execute();
            })
            ->save();
    }
}
