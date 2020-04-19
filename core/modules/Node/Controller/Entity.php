<?php

namespace SoosyzeCore\Node\Controller;

use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Node\Form\FormNode;

class Entity extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create($id_node, $entity, $req)
    {
        if (!($node = self::node()->byId($id_node))) {
            return $this->get404($req);
        }
        if (!($field_node = self::node()->getFieldRelationByEntity($entity))) {
            return $this->get404($req);
        }
        $options       = json_decode($field_node[ 'field_option' ]);
        if (!($fields_entity = self::node()->getFieldsEntity($entity))) {
            return $this->get404($req);
        }
        if (self::node()->isMaxEntity($entity, $options->foreign_key, $id_node, $options->count)) {
            return $this->get404($req);
        }

        $content = [];

        $this->container->callHook('entity.create.form.data', [ &$content ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormNode([
            'method'  => 'post',
            'action'  => self::router()->getRoute('entity.store', [
                ':id_node' => $id_node,
                ':entity'  => $entity
            ]),
            'enctype' => 'multipart/form-data' ], self::file(), self::query(), self::router(), self::config()))
            ->content($content, $entity, $fields_entity)
            ->fields()
            ->actionsEntitySubmit();

        $this->container->callHook('entity.create.form', [ &$form, $content ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'      => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Add content of type :name', [
                        ':name' => $entity ])
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-create.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($id_node, $entity, $req)
    {
        if ($req->isMaxSize()) {
            $_SESSION[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];
            $_SESSION[ 'errors_keys' ]          = [];

            return new Redirect(
                self::router()->getRoute('entity.create', [
                    ':id_node' => $id_node,
                    ':entity'  => $entity
                ])
            );
        }
        if (!($node = self::node()->byId($id_node))) {
            return $this->get404($req);
        }
        if (!($field_node = self::node()->getFieldRelationByEntity($entity))) {
            return $this->get404($req);
        }
        if (!($fields_entity = self::node()->getFieldsEntity($entity))) {
            return $this->get404($req);
        }
        $options = json_decode($field_node[ 'field_option' ]);
        if (self::node()->isMaxEntity($entity, $options->foreign_key, $id_node, $options->count)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([ 'token_entity' => 'token' ])
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());

        /* Test des champs personnalisés de la node. */
        $files = [];
        foreach ($fields_entity as $value) {
            $key = $value[ 'field_name' ];
            $validator->addRule($key, $value[ 'field_rules' ]);
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $key;
            }
        }

        $this->container->callHook('entity.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            /* Prépare les champs de la table enfant. */
            $fields = [];
            foreach ($fields_entity as $value) {
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $fieldsInsert[ $key ] = '';
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fields[ $key ] = implode(',', $validator->getInput($key, []));
                } else {
                    $fields[ $key ] = $validator->getInput($key, '');
                }
            }

            $data = self::node()->getEntity($node[ 'type' ], $node[ 'entity_id' ]);

            $fields[ $node[ 'type' ] . '_id' ] = $data[ $node[ 'type' ] . '_id' ];

            $this->container->callHook('entity.store.before', [ $validator, &$fields ]);
            self::query()
                ->insertInto('entity_' . $entity, array_keys($fields))
                ->values($fields)
                ->execute();
            $this->container->callHook('entity.store.after', [ $validator ]);

            /* Télécharge et enregistre les fichiers. */
            $id_entity = self::schema()->getIncrement('entity_' . $entity);
            foreach ($fields_entity as $value) {
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $this->saveFile($entity, $id_node, $id_entity, $value[ 'field_name' ], $validator);
                }
            }

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Your content has been saved.') ];

            return new Redirect(
                self::router()->getRoute('node.edit', [
                    ':id_node' => $id_node
                ])
            );
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($files);
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(
            self::router()->getRoute('entity.create', [
                ':id_node' => $id_node,
                ':entity'  => $entity
            ])
        );
    }

    public function edit($id_node, $entity, $id_entity, $req)
    {
        if (!($node = self::node()->byId($id_node))) {
            return $this->get404($req);
        }
        if (!($field_node = self::node()->getFieldRelationByEntity($entity))) {
            return $this->get404($req);
        }
        if (!($fields_entity = self::node()->getFieldsEntity($entity))) {
            return $this->get404($req);
        }
        if (!($content = self::node()->getEntity($entity, $id_entity))) {
            return $this->get404($req);
        }

        $this->container->callHook('entity.edit.form.data', [
            &$content, $id_node, $entity, $id_entity
        ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormNode([
            'method'  => 'post',
            'action'  => self::router()->getRoute('entity.update', [
                ':id_node'   => $id_node,
                ':entity'    => $entity,
                ':id_entity' => $id_entity
            ]),
            'enctype' => 'multipart/form-data' ], self::file(), self::query(), self::router(), self::config()))
            ->content($content, $entity, $fields_entity)
            ->fields()
            ->actionsEntitySubmit();

        $this->container->callHook('entity.edit.form', [ &$form, $content ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Edit :title content', [
                        ':title' => $entity
                    ])
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-edit.php', $this->pathViews, [ 'form' => $form ]);
    }

    public function update($id_node, $entity, $id_entity, $req)
    {
        if ($req->isMaxSize()) {
            $_SESSION[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];
            $_SESSION[ 'errors_keys' ]          = [];

            return new Redirect(
                self::router()->getRoute('entity.update', [
                    ':id_node'  => $id_node,
                    ':entity'   => $entity,
                    'id_entity' => $id_entity
                ])
            );
        }
        if (!($node = self::node()->byId($id_node))) {
            return $this->get404($req);
        }
        if (!($field_node = self::node()->getFieldRelationByEntity($entity))) {
            return $this->get404($req);
        }
        if (!($fields_entity = self::node()->getFieldsEntity($entity))) {
            return $this->get404($req);
        }
        if (!self::node()->getEntity($entity, $id_entity)) {
            return $this->get404($req);
        }
        $validator = (new Validator())
            ->setRules([ 'token_entity' => 'token' ])
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());
        /* Test des champs personnalisé de la node. */
        $files     = [];
        foreach ($fields_entity as $value) {
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $value[ 'field_type' ];
            }
            $validator->addRule($value[ 'field_name' ], $value[ 'field_rules' ]);
        }

        $this->container->callHook('entity.update.validator', [
            &$validator, $id_node, $entity, $id_entity
        ]);

        if ($validator->isValid()) {
            $fields = [];
            foreach ($fields_entity as $value) {
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    unset($fields[ $key ]);
                    $this->saveFile($entity, $id_node, $id_entity, $key, $validator);
                } elseif (in_array($value[ 'field_type' ], [ 'one_to_many' ])) {
                    unset($fields[ $key ]);
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fields[ $key ] = implode(',', $validator->getInput($key, []));
                } else {
                    $fields[ $key ] = $validator->getInput($key, '');
                }
            }

            $this->container->callHook('entity.update.before', [
                $validator, &$fields, $id_node, $entity, $id_entity
            ]);
            self::query()
                ->update('entity_' . $entity, $fields)
                ->where($entity . '_id', '==', $id_entity)
                ->execute();
            $this->container->callHook('entity.update.after', [
                $validator, $id_node, $entity, $id_entity
            ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect(
                self::router()->getRoute('node.edit', [
                    ':id_node' => $id_node
                ])
            );
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($files);
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(
            self::router()->getRoute('entity.update', [
                ':id_node'  => $id_node,
                ':entity'   => $entity,
                'id_entity' => $id_entity
            ])
        );
    }

    public function delete($id_node, $entity, $id_entity, $req)
    {
        if (!($node = self::node()->byId($id_node))) {
            return $this->get404($req);
        }
        if (!($field_node = self::node()->getFieldRelationByEntity($entity))) {
            return $this->get404($req);
        }
        if (!($fields_entity = self::node()->getFieldsEntity($entity))) {
            return $this->get404($req);
        }
        if (!self::node()->getEntity($entity, $id_entity)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([ 'id' => 'required' ])
            ->setInputs([ 'id' => $id_node ]);
        
        /* Si la node est publié. */
        if ($node['node_status_id'] === 1 && ($rules = self::node()->getRules($field_node))) {
            $entitys = self::query()
                ->from('entity_' . $entity)
                ->where($node['type'] . '_id', '==', $node['entity_id'])
                ->limit(2)
                ->fetchAll();
            /* Et que l'entité est requise, la dernière entité ne peut-être supprimé. */
            if (isset($rules['required']) && count($entitys) === 1) {
                $validator
                    ->addRule('node_status_id', '!equal:1')
                    ->addInput('node_status_id', 1);
            }
        }
        
        if ($validator->isValid()) {
            self::query()
                ->from('entity_' . $entity)
                ->delete()
                ->where($entity . '_id', '==', $id_entity)
                ->execute();
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        return new Redirect(
            self::router()->getRoute('node.edit', [
                ':id_node'   => $id_node,
                ':entity'    => $entity,
                ':id_entity' => $id_entity
            ])
        );
    }
    
    private function saveFile($type, $id_node, $id_entity, $name_field, $validator)
    {
        $dir = self::core()->getSettingEnv('files_public', 'app/files') . "/node/{$id_node}";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        self::file()
            ->add($validator->getInput($name_field), $validator->getInput("file-name-$name_field"))
            ->setPath($dir)
            ->setResolvePath()
            ->setResolveName()
            ->callGet(function ($key, $name) use ($type, $id_entity) {
                return self::query()
                    ->from('entity_' . $type)
                    ->where($type . '_id', '==', $id_entity)
                    ->fetch();
            })
            ->callMove(function ($key, $name, $move) use ($type, $id_entity, $name_field) {
                self::query()
                    ->update('entity_' . $type, [ $name_field => $move ])
                    ->where($type . '_id', '==', $id_entity)
                    ->execute();
            })
            ->callDelete(function ($key, $name) use ($type, $id_entity, $name_field) {
                self::query()
                    ->update('entity_' . $type, [ $name_field => '' ])
                    ->where($type . '_id', '==', $id_entity)
                    ->execute();
            })
            ->save();
    }
}
