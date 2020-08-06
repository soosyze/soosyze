<?php

namespace SoosyzeCore\Node\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Http\Stream;
use Soosyze\Components\Http\UploadedFile;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Node\Form\FormNode;

class Node extends \Soosyze\Controller
{
    protected $pathViews;

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function add($req)
    {
        $nodeType = self::query()
            ->from('node_type')
            ->fetchAll();

        foreach ($nodeType as $key => &$value) {
            $reqGranted = self::router()->getRequestByRoute('node.create', [
                ':node' => $value[ 'node_type' ]
            ]);
            if (!$this->container->callHook('app.granted.route', [ $reqGranted ])) {
                unset($nodeType[ $key ]);
            }
            $value[ 'link' ] = self::router()->getRoute('node.create', [
                ':node' => $value[ 'node_type' ]
            ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Add content')
                ])
                ->make('page.content', 'node-add.php', $this->pathViews, [
                    'node_type' => $nodeType
        ]);
    }

    public function create($type, $req)
    {
        if (!$fields = self::node()->getFieldsDisplay($type)) {
            return $this->get404($req);
        }

        $content = [];

        $this->container->callHook('node.create.form.data', [ &$content, $type ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormNode([
                'method'  => 'post',
                'action'  => self::router()->getRoute('node.store', [ ':node' => $type ]),
                'id'      => 'form-node',
                'enctype' => 'multipart/form-data' ], self::file(), self::query(), self::router(), self::config()))
            ->content($content, $type, $fields)
            ->make();

        $this->container->callHook('node.create.form', [ &$form, $content, $type ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'class' => 'is-invalid' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Add content of type :name', [
                        ':name' => $fields[ 0 ][ 'node_type_name' ]
                    ])
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-create.php', $this->pathViews, [
                    'form'                  => $form,
                    'node_fieldset_submenu' => $this->getNodeFieldsetSubmenu()
        ]);
    }

    public function store($type, $req)
    {
        if ($req->isMaxSize()) {
            $_SESSION[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];
            $_SESSION[ 'errors_keys' ]          = [];

            return new Redirect(self::router()->getRoute('node.create', [ ':node' => $type ]));
        }
        if (!($fields = self::node()->getFieldsForm($type))) {
            return $this->get404($req);
        }

        /* Test les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'meta_description' => '!required|string|max:512',
                'meta_noarchive'   => 'bool',
                'meta_nofollow'    => 'bool',
                'meta_noindex'     => 'bool',
                'meta_title'       => '!required|string|max:255',
                'node_status_id'   => 'required|numeric|to_int|inarray:1,2,3,4',
                'sticky'           => 'bool',
                'title'            => 'required|string|max:255|to_htmlsc',
                'token_node'       => 'token'
            ])
            ->setLabel([
                'date_created'     => t('Publication date'),
                'meta_description' => t('Description'),
                'meta_noarchive'   => t('Block caching'),
                'meta_nofollow'    => t('Block link tracking'),
                'meta_noindex'     => t('Block indexing'),
                'meta_title'       => t('Title'),
                'node_status_id'   => t('Publication status'),
                'sticky'           => t('Pin content'),
                'title'            => t('Title of the content')
            ])
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles())
            ->addInput('type', $type);

        /* Test des champs personnalisés de la node. */
        $files      = [];
        $canPublish = true;
        foreach ($fields as $value) {
            /* Si une node possède une relation requise, elle ne peut-être publié. */
            if (in_array($value[ 'field_type' ], [ 'one_to_many' ])) {
                $rules = self::node()->getRules($value);
                if (isset($rules[ 'required' ])) {
                    $canPublish = false;
                }
            } else {
                $validator->addRule($value[ 'field_name' ], $value[ 'field_rules' ])
                    ->addLabel($value[ 'field_name' ], t($value[ 'field_label' ]));
            }
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $value[ 'field_name' ];
            }
        }

        if (!$validator->getInput('date_created', false)) {
            $validator->addInput('date_created', date('Y-m-d H:i:s'));
        }
        $validator->addRule(
            'date_created',
            $validator->getInput('node_status_id') == 1
                ? 'required|date_format:Y-m-d H:i:s|date_before_or_equal:' . date('Y-m-d H:i:s')
                : '!required|date_format:Y-m-d H:i:s'
        );

        /* Ne peut pas publier la node si les règles des relations ne sont pas respectées. */
        if (!$canPublish) {
            $validator->addRule('node_status_id', '!accepted');
        }

        $this->container->callHook('node.store.validator', [ &$validator, $type ]);

        if ($validator->isValid()) {
            /* Prépare les champs de la table enfant. */
            $fieldsInsert   = [];
            $fieldsRelation = false;
            foreach ($fields as $value) {
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $fieldsInsert[ $key ] = '';
                } elseif (in_array($value[ 'field_type' ], [ 'one_to_many' ])) {
                    $fieldsRelation = true;
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fieldsInsert[ $key ] = implode(',', $validator->getInput($key, []));
                } else {
                    $fieldsInsert[ $key ] = $validator->getInput($key, '');
                }
            }

            $this->container->callHook('node.entity.store.before', [ $validator, &$fieldsInsert, $type ]);
            self::query()
                ->insertInto('entity_' . $type, array_keys($fieldsInsert))
                ->values($fieldsInsert)
                ->execute();
            $this->container->callHook('node.entity.store.after', [ $validator, $type ]);

            /* Rassemble les champs personnalisés dans la node. */
            $node = [
                'date_changed'     => time(),
                'date_created'     => strtotime($validator->getInput('date_created')),
                'entity_id'        => self::schema()->getIncrement('entity_' . $type),
                'meta_description' => $validator->getInput('meta_description'),
                'meta_noarchive'   => (bool) $validator->getInput('meta_noarchive'),
                'meta_nofollow'    => (bool) $validator->getInput('meta_nofollow'),
                'meta_noindex'     => (bool) $validator->getInput('meta_noindex'),
                'meta_title'       => $validator->getInput('meta_title'),
                'sticky'           => (bool) $validator->getInput('sticky'),
                'node_status_id'   => $validator->getInput('node_status_id'),
                'title'            => $validator->getInput('title'),
                'type'             => $type,
            ];

            $this->container->callHook('node.store.before', [ $validator, &$node ]);
            self::query()
                ->insertInto('node', array_keys($node))
                ->values($node)
                ->execute();
            $this->container->callHook('node.store.after', [ $validator ]);

            /* Télécharge et enregistre les fichiers. */
            $node[ 'id' ] = self::schema()->getIncrement('node');

            foreach ($fields as $value) {
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $this->saveFile($node, $value[ 'field_name' ], $validator);
                }
            }

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Your content has been saved.') ];

            $idNode = self::schema()->getIncrement('node');

            return new Redirect(
                $fieldsRelation
                ? self::router()->getRoute('node.edit', [ ':id_node' => $idNode ])
                : self::router()->getRoute('node.index')
            );
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($files);
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(self::router()->getRoute('node.create', [ ':node' => $type ]));
    }

    public function show($idNode, $req)
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        $fields = self::node()->makeFieldsById($node[ 'type' ], $node[ 'entity_id' ]);

        $tpl = self::template()
                ->view('this', [
                    'title'       => $node[ 'meta_title' ],
                    'description' => $node[ 'meta_description' ],
                ])
                ->view('page', [
                    'title_main' => $node[ 'title' ],
                ])
                ->make('page.content', 'node-show.php', $this->pathViews, [
                    'fields'       => $fields,
                    'node'         => $node,
                    'node_submenu' => $this->getSubmenuNode($node, 'node.show')
                ])->override('page.content', [ 'node-show-' . $idNode . '.php', 'node-show-' . $node[ 'type' ] . '.php' ]);

        $this->container->callHook('node.show.tpl', [ &$tpl, $node, $idNode ]);

        return $tpl;
    }

    public function edit($idNode, $req)
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        if (!($fields = self::node()->getFieldsDisplay($node[ 'type' ]))) {
            return $this->get404($req);
        }

        $content = $node + self::node()->getEntity($node[ 'type' ], $node[ 'entity_id' ]);

        $this->container->callHook('node.edit.form.data', [ &$content, $idNode ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormNode([
                'method'  => 'post',
                'action'  => self::router()->getRoute('node.update', [ ':id_node' => $idNode ]),
                'id'      => 'form-node',
                'enctype' => 'multipart/form-data' ], self::file(), self::query(), self::router(), self::config()))
            ->content($content, $content[ 'type' ], $fields)
            ->make();

        $this->container->callHook('node.edit.form', [ &$form, $content ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'class' => 'is-invalid' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Edit :title content', [ ':title' => $content[ 'title' ] ])
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-edit.php', $this->pathViews, [
                    'form'                  => $form,
                    'node_submenu'          => $this->getSubmenuNode($node, 'node.edit'),
                    'node_fieldset_submenu' => $this->getNodeFieldsetSubmenu()
        ]);
    }

    public function update($idNode, $req)
    {
        if ($req->isMaxSize()) {
            $_SESSION[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];
            $_SESSION[ 'errors_keys' ]          = [];

            return new Redirect(
                self::router()->getRoute('node.edit', [
                    ':id_node' => $idNode
                ])
            );
        }
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        if (!($fields = self::node()->getFieldsForm($node[ 'type' ]))) {
            return $this->get404($req);
        }

        /* Test les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'meta_description' => '!required|string|max:512',
                'meta_noarchive'   => 'bool',
                'meta_nofollow'    => 'bool',
                'meta_noindex'     => 'bool',
                'meta_title'       => '!required|string|max:255',
                'node_status_id'   => 'required|numeric|to_int|inarray:1,2,3,4',
                'sticky'           => 'bool',
                'title'            => 'required|string|max:255|to_htmlsc',
                'token_node'       => 'token'
            ])
            ->setLabel([
                'date_created'     => t('Publication date'),
                'meta_description' => t('Description'),
                'meta_noarchive'   => t('Block caching'),
                'meta_nofollow'    => t('Block link tracking'),
                'meta_noindex'     => t('Block indexing'),
                'meta_title'       => t('Title'),
                'node_status_id'   => t('Publication status'),
                'sticky'           => t('Pin content'),
                'title'            => t('Title of the content')
            ])
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles())
            ->addInput('type', $node[ 'type' ]);

        /* Test des champs personnalisé de la node. */
        $files      = [];
        $canPublish = true;
        foreach ($fields as $value) {
            /* Si une node possède une relation requise, elle ne peut-être publié. */
            if (in_array($value[ 'field_type' ], [ 'one_to_many' ])) {
                if ($rules = self::node()->getRules($value)) {
                    $options = json_decode($value[ 'field_option' ], true);
                    $entitys = self::query()
                        ->from($options[ 'relation_table' ])
                        ->where($options[ 'foreign_key' ], '==', $node[ 'entity_id' ])
                        ->limit(2)
                        ->fetchAll();
                    if (!empty($rules[ 'required' ]) && count($entitys) < 1) {
                        $canPublish = false;
                    }
                }
            } else {
                $validator
                    ->addRule($value[ 'field_name' ], $value[ 'field_rules' ])
                    ->addLabel($value[ 'field_name' ], t($value[ 'field_label' ]));
            }
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $value[ 'field_name' ];
            }
        }

        if (!$validator->getInput('date_created', false)) {
            $validator->addInput('date_created', date('Y-m-d H:i:s'));
        }
        $validator->addRule(
            'date_created',
            $validator->getInput('node_status_id') == 1
                ? 'required|date_format:Y-m-d H:i:s|date_before_or_equal:' . date('Y-m-d H:i:s')
                : '!required|date_format:Y-m-d H:i:s'
        );

        /* Ne peut pas publier la node si les règles des relations ne sont pas respectées. */
        if (!$canPublish) {
            $validator->addRule('node_status_id', '!accepted');
        }

        $this->container->callHook('node.update.validator', [ &$validator, $idNode ]);

        if ($validator->isValid()) {
            $fieldsUpdate = [];
            foreach ($fields as $value) {
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $this->saveFile($node, $key, $validator);
                } elseif (in_array($value[ 'field_type' ], [ 'one_to_many' ])) {
                    $this->updateWeightEntity($value, $validator->getInput($key, [
                    ]));
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fieldsUpdate[ $key ] = implode(',', $validator->getInput($key, [
                        ]));
                } else {
                    $fieldsUpdate[ $key ] = $validator->getInput($key, '');
                }
            }

            $this->container->callHook('node.entity.update.before', [
                $validator, &$fieldsUpdate, $node, $idNode
            ]);
            self::query()
                ->update('entity_' . $node[ 'type' ], $fieldsUpdate)
                ->where($node[ 'type' ] . '_id', '==', $node[ 'entity_id' ])
                ->execute();
            $this->container->callHook('node.entity.update.after', [
                $validator, $node, $idNode
            ]);

            $value = [
                'date_changed'     => time(),
                'date_created'     => strtotime($validator->getInput('date_created')),
                'meta_description' => $validator->getInput('meta_description'),
                'meta_noarchive'   => (bool) $validator->getInput('meta_noarchive'),
                'meta_nofollow'    => (bool) $validator->getInput('meta_nofollow'),
                'meta_noindex'     => (bool) $validator->getInput('meta_noindex'),
                'meta_title'       => $validator->getInput('meta_title'),
                'node_status_id'   => (int) $validator->getInput('node_status_id'),
                'sticky'           => (bool) $validator->getInput('sticky'),
                'title'            => $validator->getInput('title')
            ];

            $this->container->callHook('node.update.before', [
                $validator, &$value, $idNode
            ]);
            self::query()
                ->update('node', $value)
                ->where('id', '==', $idNode)
                ->execute();
            $this->container->callHook('node.update.after', [ $validator, $idNode ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($files);
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        return new Redirect(
            self::router()->getRoute('node.edit', [
                ':id_node' => $idNode
            ])
        );
    }

    public function remove($idNode, $req)
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }

        $this->container->callHook('node.remove.form.data', [ &$node, $idNode ]);

        $form = (new FormBuilder([
                'method' => 'post',
                'action' => self::router()->getRoute('node.delete', [ ':id_node' => $idNode ])
                ]))
            ->group('node-remove-information-fieldset', 'fieldset', function ($form) {
                $form->legend('node-remove-information-legend', t('Node deletion'))
                ->html('system-favicon-info-dimensions', '<p:attr>:_content</p>', [
                    '_content' => t('Warning ! The deletion of the node is final.')
                ]);
            })
            ->token('token_node_remove')
            ->submit('sumbit', t('Delete'), [ 'class' => 'btn btn-danger' ]);

        $this->container->callHook('node.remove.form', [ &$form, $node, $idNode ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Delete :name content', [ ':name' => $node[ 'title' ] ])
                ])
                ->make('page.content', 'node-remove.php', $this->pathViews, [
                    'form'         => $form,
                    'node_submenu' => $this->getSubmenuNode($node, 'node.delete')
        ]);
    }

    public function delete($idNode, $req)
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([ 'id' => 'required' ])
            ->setInputs([ 'id' => $idNode ]);

        $this->container->callHook('node.delete.validator', [ &$validator, $idNode ]);

        if ($validator->isValid()) {
            $this->container->callHook('node.delete.before', [ $validator, $idNode ]);
            $this->deleteRelation($node);

            self::query()
                ->from('node')
                ->delete()
                ->where('id', '==', $idNode)
                ->execute();

            $this->deleteFile($node['type'], $idNode);
            $this->container->callHook('node.delete.after', [ $validator, $idNode ]);
        }

        return new Redirect(self::router()->getRoute('node.index'));
    }

    public function cloneNode($idNode, $req)
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        $type   = $node[ 'type' ];
        if (!($entity = self::node()->getEntity($type, $node[ 'entity_id' ]))) {
            return $this->get404($req);
        }
        if (!($fields = self::node()->getFieldsform($type))) {
            return $this->get404($req);
        }
        if (mb_strlen($node[ 'title' ] . ' clone') > 255) {
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'Clone content title is too long' ];

            return new Redirect(self::router()->getRoute('node.index'));
        }

        $entityClone = $entity;
        /* Construit l'entity principale */
        unset($entityClone[ $type . '_id' ]);
        self::query()
            ->insertInto('entity_' . $type, array_keys($entityClone))
            ->values($entityClone)
            ->execute();
        $entityId    = self::schema()->getIncrement('entity_' . $type);

        /* Construit la node */
        unset($node[ 'id' ], $node[ 'node_status_id' ]);
        $node[ 'entity_id' ]    = $entityId;
        $node[ 'title' ]        = $node[ 'title' ] . ' clone';
        $node[ 'date_created' ] = time();
        $node[ 'date_changed' ] = time();

        self::query()
            ->insertInto('node', array_keys($node))
            ->values($node)
            ->execute();
        $nodeId = self::schema()->getIncrement('node');

        /* Parcours les champs de l'entité principal. */
        foreach ($fields as $value) {
            $fieldName = $value[ 'field_name' ];
            /* Copie ses fichiers. */
            if (in_array($value[ 'field_type' ], [ 'file', 'image' ])) {
                $dir  = self::core()->getSettingEnv('files_public', 'app/files') . "/node/$type/$nodeId";
                $file = $entity[ $fieldName ];

                if (!is_file($file)) {
                    continue;
                }

                $upload = new UploadedFile(
                    new Stream(fopen($file, 'r')),
                    self::getBasename($file)
                );
                self::file()
                    ->add($upload)
                    ->setPath($dir)
                    ->setResolvePath()
                    ->setResolveName()
                    ->callMove(function ($key, $name, $move) use ($type, $entityId, $fieldName) {
                        self::query()
                        ->update('entity_' . $type, [ $fieldName => $move ])
                        ->where($type . '_id', '==', $entityId)
                        ->execute();
                    })
                    ->saveOne();
            }
            /* Si elle possède des sous entités. */
            elseif (in_array($value[ 'field_type' ], [ 'one_to_many' ])) {
                $options      = !empty($value[ 'field_option' ])
                    ? json_decode($value[ 'field_option' ], true)
                    : [];
                $dataRelation = self::query()
                    ->from($options[ 'relation_table' ])
                    ->where($options[ 'foreign_key' ], $entity[ $options[ 'foreign_key' ] ])
                    ->fetchAll();

                $fieldsFile = self::query()
                    ->from('node_type_field')
                    ->leftJoin('field', 'field_id', 'field.field_id')
                    ->where('node_type', $fieldName)
                    ->in('field_type', [ 'file', 'image' ])
                    ->fetchAll();

                /* Parcours toutes les sous entités. */
                foreach ($dataRelation as $data) {
                    foreach ($fieldsFile as $file) {
                        $fieldName = $file[ 'field_name' ];
                        /* Parcours ses fichiers pour les copier. */
                        if (isset($data[ $fieldName ])) {
                            $dir      = self::core()->getSettingEnv('files_public', 'app/files') . "/node/$type/$nodeId/{$file['node_type']}";
                            $pathFile = $data[ $fieldName ];

                            if (!is_file($pathFile)) {
                                continue;
                            }
                            $upload = new UploadedFile(
                                new Stream(fopen($pathFile, 'r')),
                                self::getBasename($pathFile)
                            );

                            self::file()
                                ->add($upload)
                                ->setPath($dir)
                                ->setResolvePath()
                                ->setResolveName()
                                ->callMove(function ($key, $name, $move) use (&$data, $fieldName) {
                                    $data[ $fieldName ] = $move;
                                })
                                ->saveOne();
                        }
                    }
                    unset($data[ $value[ 'field_name' ] . '_id' ]);
                    $data[ $options[ 'foreign_key' ] ] = $entityId;
                    self::query()
                        ->insertInto($options[ 'relation_table' ], array_keys($data))
                        ->values($data)
                        ->execute();
                }
            }
        }

        return new Redirect(self::router()->getRoute('node.index'));
    }

    public static function getBasename($pathFile)
    {
        return strtolower(pathinfo($pathFile, PATHINFO_BASENAME));
    }

    public function getSubmenuNode(array $node, $keyRoute)
    {
        $menu = [
            [
                'key'        => 'node.edit',
                'request'    => self::router()->getRequestByRoute('node.edit', [
                    ':id_node' => $node[ 'id' ]
                ]),
                'title_link' => t('Edit')
            ], [
                'key'        => 'node.delete',
                'request'    => self::router()->getRequestByRoute('node.remove', [
                    ':id_node' => $node[ 'id' ]
                ]),
                'title_link' => t('Delete')
            ]
        ];

        $this->container->callHook('node.submenu', [ &$menu, $node[ 'id' ] ]);

        foreach ($menu as $key => &$link) {
            if ($this->container->callHook('app.granted.route', [ $link[ 'request' ] ])) {
                $link[ 'link' ] = $link[ 'request' ]->getUri();

                continue;
            }

            unset($menu[ $key ]);
        }
        if ($menu) {
            $nodeShow = [
                'key'        => 'node.show',
                'request'    => self::router()->getRequestByRoute('node.show', [
                    ':id_node' => $node[ 'id' ]
                ]),
                'title_link' => t('View')
            ];
            if ($this->container->callHook('app.granted.route', [ $nodeShow[ 'request' ] ])) {
                $nodeShow[ 'link' ] = $nodeShow[ 'request' ]->getUri();
                $menu               = array_merge([ $nodeShow ], $menu);
            }
        }

        return self::template()
                ->createBlock('submenu-node.php', $this->pathViews)
                ->addVars([
                    'key_route' => $keyRoute,
                    'menu'      => $menu
        ]);
    }
    
    public function getNodeFieldsetSubmenu()
    {
        $menu = [
            [
                'class'      => 'active',
                'link'       => '#fields-fieldset',
                'title_link' => t('Content')
            ], [
                'class'      => '',
                'link'       => '#seo-fieldset',
                'title_link' => t('SEO')
            ], [
                'class'      => '',
                'link'       => '#publication-fieldset',
                'title_link' => t('Publication')
            ]
        ];
        
        if (self::module()->has('Menu')) {
            $menu[] = [
                    'class'      => '',
                    'link'       => '#menu-fieldset',
                    'title_link' => t('Menu')
            ];
        }

        $this->container->callHook('node.fieldset.submenu', [ &$menu ]);

        return self::template()
                ->createBlock('submenu-node_fieldset.php', $this->pathViews)
                ->addVar('menu', $menu);
    }

    private function deleteFile($type, $idNode)
    {
        $dir = self::core()->getSettingEnv('files_public', 'app/files') . "/node/$type/$idNode";
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

    private function deleteRelation($node)
    {
        /* Suppression des relations */
        $entity = self::node()->getEntity($node[ 'type' ], $node[ 'entity_id' ]);

        $relationNode = self::query()
            ->from('node_type_field')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $node[ 'type' ])
            ->where('field_type', 'one_to_many')
            ->fetchAll();
        foreach ($relationNode as $relation) {
            $options = json_decode($relation[ 'field_option' ], true);
            self::query()
                ->from($options[ 'relation_table' ])
                ->delete()
                ->where($options[ 'foreign_key' ], $entity[ $options[ 'local_key' ] ])
                ->execute();
        }

        /* Supression du contenu. */
        self::query()
            ->from('entity_' . $node[ 'type' ])
            ->delete()
            ->where($node[ 'type' ] . '_id', '==', $node[ 'entity_id' ])
            ->execute();
    }

    private function updateWeightEntity(array $field, array $data)
    {
        $options = json_decode($field[ 'field_option' ], true);
        if ($options[ 'sort' ] !== 'weight') {
            return;
        }
        foreach ($data as $value) {
            self::query()
                ->update('entity_' . $field[ 'field_name' ], [
                    'weight' => $value[ 'weight' ]
                ])
                ->where($field[ 'field_name' ] . '_id', '==', $value[ 'id' ])
                ->execute();
        }
    }

    private function saveFile($node, $nameField, $validator)
    {
        $dir = self::core()->getSettingEnv('files_public', 'app/files') . "/node/{$node[ 'type' ]}/{$node[ 'id' ]}";

        self::file()
            ->add($validator->getInput($nameField), $validator->getInput("file-$nameField-name"))
            ->setName($nameField)
            ->setPath($dir)
            ->setResolvePath()
            ->callGet(function ($key, $name) use ($node) {
                return self::query()
                    ->from('entity_' . $node[ 'type' ])
                    ->where($node[ 'type' ] . '_id', '==', $node[ 'entity_id' ])
                    ->fetch()[ $key ];
            })
            ->callMove(function ($key, $name, $move) use ($node) {
                self::query()
                ->update('entity_' . $node[ 'type' ], [ $key => $move ])
                ->where($node[ 'type' ] . '_id', '==', $node[ 'entity_id' ])
                ->execute();
            })
            ->callDelete(function ($key, $name) use ($node) {
                self::query()
                ->update('entity_' . $node[ 'type' ], [ $key => '' ])
                ->where($node[ 'type' ] . '_id', '==', $node[ 'entity_id' ])
                ->execute();
            })
            ->save();
    }
}
