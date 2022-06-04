<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Node\Form\FormNode;
use SoosyzeCore\Node\Form\FormNodeDelete;
use SoosyzeCore\Node\Model\Field\OneToManyOption;
use SoosyzeCore\Template\Services\Block;

/**
 * @method \SoosyzeCore\System\Services\Alias        alias()
 * @method \SoosyzeCore\FileSystem\Services\file     file()
 * @method \SoosyzeCore\Node\Services\Node           node()
 * @method \SoosyzeCore\Node\Services\NodeUser       nodeuser()
 * @method \SoosyzeCore\QueryBuilder\Services\Schema schema()
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 * @method \SoosyzeCore\User\Services\User           user()
 *
 * @phpstan-import-type NodeEntity from \SoosyzeCore\Node\Extend
 *
 * @phpstan-type Submenu array{
 *      key: string,
 *      link?: string,
 *      request: RequestInterface,
 *      title_link: string
 * }
 */
class Node extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function add(ServerRequestInterface $req): ResponseInterface
    {
        $nodeType = self::query()
            ->from('node_type')
            ->orderBy('node_type_name')
            ->fetchAll();

        foreach ($nodeType as $key => &$value) {
            $reqGranted = self::router()->generateRequest('node.create', [
                'nodeType' => $value[ 'node_type' ]
            ]);
            if (!$this->container->callHook('app.granted.request', [ $reqGranted ])) {
                unset($nodeType[ $key ]);
            }
            $value[ 'link' ] = self::router()->generateUrl('node.create', [
                'nodeType' => $value[ 'node_type' ]
            ]);
        }
        unset($value);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Add content')
                ])
                ->make('page.content', 'node/content-node-add.php', $this->pathViews, [
                    'node_type' => $nodeType
        ]);
    }

    public function create(string $nodeType, ServerRequestInterface $req): ResponseInterface
    {
        if (!$fields = self::node()->getFieldsDisplay($nodeType)) {
            return $this->get404($req);
        }

        $values = [];

        $this->container->callHook('node.create.form.data', [ &$values, $nodeType ]);

        $form = (new FormNode([
                'action'        => self::router()->generateUrl('node.store', [ 'nodeType' => $nodeType ]),
                'data-tab-pane' => '.pane-node',
                'enctype'       => 'multipart/form-data',
                'id'            => 'form-node',
                'method'  => 'post' ], self::file(), self::query(), self::router(), self::config()))
            ->setValues($values)
            ->setFields($fields)
            ->setUserCurrent(self::user()->isConnected())
            ->setDisabledUserCurrent(!self::user()->isGranted('node.user.edit'))
            ->makeFields();

        $this->container->callHook('node.create.form', [ &$form, $values, $nodeType ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Add content of type :name', [
                        ':name' => $fields[ 0 ][ 'node_type_name' ]
                    ])
                ])
                ->make('page.content', 'node/content-node-form.php', $this->pathViews, [
                    'form'                  => $form,
                    'node_fieldset_submenu' => $this->getNodeFieldsetSubmenu()
                ])
                ->override('page.content', [ 'node/content-node-form_create.php' ]);
    }

    public function store(string $nodeType, ServerRequestInterface $req): ResponseInterface
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
        if (!($fields = self::node()->getFieldsForm($nodeType))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        /* Test les champs par defauts de la node. */
        $validator = $this->getValidator($req, $nodeType, $fields);

        $this->container->callHook('node.store.validator', [ &$validator, $nodeType ]);

        if ($validator->isValid()) {
            /* Prépare les champs de la table enfant. */
            $fieldsInsert   = [];
            $fieldsRelation = false;
            foreach ($fields as $value) {
                /** @phpstan-var string $key */
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $fieldsInsert[ $key ] = '';
                } elseif ($value[ 'field_type' ] === 'one_to_many') {
                    $fieldsRelation = true;
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fieldsInsert[ $key ] = implode(',', $validator->getInputArray($key));
                } else {
                    $fieldsInsert[ $key ] = $validator->getInputString($key);
                }
            }

            $this->container->callHook('node.entity.store.before', [ $validator, &$fieldsInsert, $nodeType ]);
            self::query()
                ->insertInto('entity_' . $nodeType, array_keys($fieldsInsert))
                ->values($fieldsInsert)
                ->execute();
            $this->container->callHook('node.entity.store.after', [ $validator, $nodeType ]);

            /* Rassemble les champs personnalisés dans la node. */
            $data = $this->getData($validator, $nodeType);

            $this->container->callHook('node.store.before', [ $validator, &$data ]);
            self::query()
                ->insertInto('node', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('node.store.after', [ $validator ]);

            /* Télécharge et enregistre les fichiers. */
            $data[ 'id' ] = self::schema()->getIncrement('node');

            foreach ($fields as $value) {
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $this->saveFile($data, $value[ 'field_name' ], $validator);
                }
            }

            $_SESSION[ 'messages' ][ 'success' ][] = t('Your content has been saved.');

            return $this->json(201, [
                'redirect' => $fieldsRelation
                    ? self::router()->generateUrl('node.edit', [ 'idNode' => $data[ 'id' ] ])
                    : self::router()->generateUrl('node.admin')
            ]);
        }

        $errorsKeys = $validator->getKeyInputErrors();

        if (in_array('date_created', $errorsKeys)) {
            $errorsKeys[] = 'date';
            $errorsKeys[] = 'date_time';
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $errorsKeys
        ]);
    }

    public function show(int $idNode, ServerRequestInterface $req): ResponseInterface
    {
        if (!($node = self::node()->getCurrentNode($idNode))) {
            return $this->get404($req);
        }

        $fields = self::node()->makeFieldsById($node[ 'type' ], $node[ 'entity_id' ]);
        $user   = self::nodeuser()->getInfosUser($node);

        if ($node[ 'node_status_id' ] != 1) {
            $_SESSION[ 'messages' ][ 'infos' ][] = t('This content is not published');
        }

        $tpl = self::template()
                ->view('this', [
                    'description' => $node[ 'meta_description' ],
                    'title'       => $node[ 'meta_title' ]
                ])
                ->addMetas($this->getMeta($node, $fields))
                ->view('page', [
                    'fields'     => $fields,
                    'node'       => $node,
                    'title_main' => $node[ 'title' ],
                    'user'       => $user
                ])
                ->override('page', [
                    'page-node-show_' . $node[ 'type' ] . '.php',
                    'page-node.php'
                ])
                ->view('page.submenu', $this->getSubmenuNode('node.show', $idNode))
                ->make('page.content', 'node/content-node-show.php', $this->pathViews, [
                    'fields' => $fields,
                    'node'   => $node,
                    'user'   => $user
                ])->override('page.content', [
                    'node/content-node-show_' . $idNode . '.php',
                    'node/content-node-show_' . $node[ 'type' ] . '.php'
                ]);

        $this->container->callHook('node.show.tpl', [ &$tpl, $node, $idNode ]);

        return $tpl;
    }

    public function edit(int $idNode, ServerRequestInterface $req): ResponseInterface
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }
        if (!($fields = self::node()->getFieldsDisplay($node[ 'type' ]))) {
            return $this->get404($req);
        }

        $values = $node + (self::node()->getEntity($node[ 'type' ], $node[ 'entity_id' ]) ?? []);

        $this->container->callHook('node.edit.form.data', [ &$values, $idNode ]);

        $form = (new FormNode([
                'action'        => self::router()->generateUrl('node.update', [ 'idNode' => $idNode ]),
                'data-tab-pane' => '.pane-node',
                'enctype'       => 'multipart/form-data',
                'id'            => 'form-node',
                'method'        => 'put' ], self::file(), self::query(), self::router(), self::config()))
            ->setValues($values)
            ->setFields($fields)
            ->setDisabledUserCurrent(!self::user()->isGranted('node.user.edit'))
            ->makeFields();

        $this->container->callHook('node.edit.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Edit :title content', [ ':title' => $values[ 'title' ] ])
                ])
                ->view('page.submenu', $this->getSubmenuNode('node.edit', $idNode))
                ->make('page.content', 'node/content-node-form.php', $this->pathViews, [
                    'form'                  => $form,
                    'node_fieldset_submenu' => $this->getNodeFieldsetSubmenu()
                ])
                ->override('page.content', [ 'node/content-node-form_edit.php' ]);
    }

    public function update(int $idNode, ServerRequestInterface $req): ResponseInterface
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
        if (!($node = self::node()->getCurrentNode($idNode))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }
        if (!($fields = self::node()->getFieldsForm($node[ 'type' ]))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = $this->getValidator($req, $node[ 'type' ], $fields, $idNode);

        $this->container->callHook('node.update.validator', [ &$validator, $idNode ]);

        if ($validator->isValid()) {
            $fieldsUpdate = [];
            foreach ($fields as $value) {
                /** @phpstan-var string $key */
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $this->saveFile($node, $key, $validator);
                } elseif ($value[ 'field_type' ] === 'one_to_many') {
                    $this->updateWeightEntity($value, $validator->getInputArray($key));
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fieldsUpdate[ $key ] = implode(',', $validator->getInputArray($key));
                } else {
                    $fieldsUpdate[ $key ] = $validator->getInputString($key);
                }
            }

            $this->container->callHook('node.entity.update.before', [
                $validator, &$fieldsUpdate, $node, $idNode
            ]);
            self::query()
                ->update('entity_' . $node[ 'type' ], $fieldsUpdate)
                ->where($node[ 'type' ] . '_id', '=', $node[ 'entity_id' ])
                ->execute();
            $this->container->callHook('node.entity.update.after', [
                $validator, $node, $idNode
            ]);

            $data = $this->getData($validator);

            $this->container->callHook('node.update.before', [
                $validator, &$data, $idNode
            ]);
            self::query()
                ->update('node', $data)
                ->where('id', '=', $idNode)
                ->execute();
            $this->container->callHook('node.update.after', [ $validator, $idNode ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('node.edit', [
                        'idNode' => $idNode
                    ])
            ]);
        }

        $errorsKeys = $validator->getKeyInputErrors();

        if (in_array('date_created', $errorsKeys)) {
            $errorsKeys[] = 'date';
            $errorsKeys[] = 'date_time';
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $errorsKeys
        ]);
    }

    public function remove(int $idNode, ServerRequestInterface $req): ResponseInterface
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->get404($req);
        }

        $values[ 'current_path' ] = self::alias()->getAlias('node/' . $node[ 'id' ], 'node/' . $node[ 'id' ]);

        $pathsSettings = self::node()->getPathSettings();

        $useInPath = null;
        foreach ($pathsSettings as $value) {
            if (!empty($value[ 'path' ]) && self::alias()->getSource($value[ 'path' ], $value[ 'path' ]) === 'node/' . $idNode) {
                $useInPath = $value;

                break;
            }
        }

        $this->container->callHook('node.remove.form.data', [ &$node, $idNode ]);

        $action = self::router()->generateUrl('node.delete', [ 'idNode' => $idNode ]);

        $form = (new FormNodeDelete([ 'action' => $action, 'method' => 'delete' ], self::router()))
            ->setValues($values)
            ->setUseInPath($useInPath)
            ->makeFields();

        $this->container->callHook('node.remove.form', [ &$form, $node, $idNode ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-file" aria-hidden="true"></i>',
                    'title_main' => t('Delete :name content', [ ':name' => $node[ 'title' ] ])
                ])
                ->view('page.submenu', $this->getSubmenuNode('node.delete', $idNode))
                ->make('page.content', 'node/content-node-form.php', $this->pathViews, [
                    'form' => $form,
                ])
                ->override('page.content', [ 'node/content-node-form_remove.php' ]);
    }

    public function delete(int $idNode, ServerRequestInterface $req): ResponseInterface
    {
        if (!($node = self::node()->byId($idNode))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->setRules([
                'files' => 'bool',
                'id'    => 'required'
            ])
            ->setInputs([ 'id' => $idNode ] + (array) $req->getParsedBody());

        $pathsSettings = self::node()->getPathSettings();

        foreach ($pathsSettings as $value) {
            if (empty($value[ 'path' ]) && self::alias()->getSource($value[ 'path' ], $value[ 'path' ]) !== 'node/' . $idNode) {
                continue;
            }

            $not = empty($value[ 'required' ])
                ? ''
                : '!';

            $currentAlias = self::alias()->getalias('node/' . $idNode, 'node/' . $idNode);

            $validator
                ->addRule('path', $not . "required|route|!equal:$currentAlias|!equal:node/$idNode")
                ->addInput('path_key', $value[ 'key' ])
                ->addRule('path_key', $not . 'required|string')
                ->addLabel('path', t('New path for') . ' ' . t($value[ 'title' ]))
                ->setMessages([
                    'path' => [
                        'equal' => [
                            'not' => t('You cannot enter the URL of the content that is going to be deleted.')
                        ]
                    ]
                ]);

            break;
        }

        $this->container->callHook('node.delete.validator', [ &$validator, $idNode ]);

        if ($validator->isValid()) {
            $this->container->callHook('node.delete.before', [ $validator, $idNode ]);
            self::node()->deleteRelation($node);

            self::query()
                ->from('node')
                ->delete()
                ->where('id', '=', $idNode)
                ->execute();

            if ((bool) $validator->getInput('files')) {
                self::node()->deleteFile($node[ 'type' ], $idNode);
            }
            $this->container->callHook('node.delete.after', [ $validator, $idNode ]);

            if ($validator->getInput('path')) {
                self::config()->set(
                    $validator->getInputString('path_key'),
                    $validator->getInputString('path')
                );
            }

            $_SESSION[ 'messages' ][ 'success' ][] = t('Content :title has been deleted', [ ':title' => $node[ 'title' ] ]);

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('node.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function getSubmenuNode(string $keyRoute, int $idNode): array
    {
        /** @phpstan-var array<Submenu> $menu */
        $menu = [
            [
                'key'        => 'node.edit',
                'request'    => self::router()->generateRequest('node.edit', [
                    'idNode' => $idNode
                ]),
                'title_link' => t('Edit')
            ], [
                'key'        => 'node.delete',
                'request'    => self::router()->generateRequest('node.remove', [
                    'idNode' => $idNode
                ]),
                'title_link' => t('Delete')
            ]
        ];

        $this->container->callHook('node.submenu', [ &$menu, $idNode ]);

        foreach ($menu as $key => &$link) {
            if ($this->container->callHook('app.granted.request', [ $link[ 'request' ] ])) {
                $link[ 'link' ] = $link[ 'request' ]->getUri();

                continue;
            }

            unset($menu[ $key ]);
        }
        unset($link);

        if ($menu !== []) {
            $nodeShow = [
                'key'        => 'node.show',
                'request'    => self::router()->generateRequest('node.show', [
                    'idNode' => $idNode
                ]),
                'title_link' => t('View')
            ];
            if ($this->container->callHook('app.granted.request', [ $nodeShow[ 'request' ] ])) {
                /** @phpstan-var string $alias */
                $alias     = self::alias()->getAlias('node/' . $idNode, 'node/' . $idNode);
                $pathIndex = self::config()->get('settings.path_index');

                $nodeShow[ 'link' ] = self::router()->makeUrl(
                    in_array($pathIndex, [ $alias, 'node/' . $idNode ])
                        ? ''
                        : '/' . ltrim($alias, '/')
                );

                $menu = array_merge([ $nodeShow ], $menu);
            }
        }

        return [ 'key_route' => $keyRoute, 'menu' => $menu ];
    }

    public function getNodeFieldsetSubmenu(): Block
    {
        $menu = [
            [
                'class'      => 'active',
                'link'       => '#fields-fieldset',
                'title_link' => t('Content')
            ], [
                'class'      => '',
                'link'       => '#publication-fieldset',
                'title_link' => t('Publication')
            ], [
                'class'      => '',
                'link'       => '#user-fieldset',
                'title_link' => t('User')
            ], [
                'class'      => '',
                'link'       => '#seo-fieldset',
                'title_link' => t('SEO')
            ], [
                'class'      => '',
                'link'       => '#url-fieldset',
                'title_link' => t('Url')
            ]
        ];

        $this->container->callHook('node.fieldset.submenu', [ &$menu ]);

        return self::template()
                ->createBlock('node/submenu-node_fieldset.php', $this->pathViews)
                ->addVar('menu', $menu);
    }

    private function getValidator(ServerRequestInterface $req, string $type, array $fields, int $idNode = null): Validator
    {
        $node      = self::node()->getCurrentNode($idNode);
        /* Test les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'date_created'     => 'required|date_format:Y-m-d H:i',
                'meta_description' => '!required|string|max:512',
                'meta_noarchive'   => 'bool',
                'meta_nofollow'    => 'bool',
                'meta_noindex'     => 'bool',
                'meta_title'       => '!required|string|max:255',
                'node_status_id'   => 'required|numeric|to_int|inarray:1,2,3,4',
                'sticky'           => 'bool',
                'title'            => 'required|string|max:255',
                'user_id'          => '!required|numeric|inarray:' . $this->getListUsersId()
            ])
            ->setLabels([
                'date_created'     => t('Publication date'),
                'meta_description' => t('Description'),
                'meta_noarchive'   => t('Block caching'),
                'meta_nofollow'    => t('Block link tracking'),
                'meta_noindex'     => t('Block indexing'),
                'meta_title'       => t('Title'),
                'node_status_id'   => t('Publication status'),
                'sticky'           => t('Pin content'),
                'title'            => t('Title of the content'),
                'user_id'          => t('User')
            ])
            ->setInputs((array) $req->getParsedBody() + $req->getUploadedFiles())
            ->addInput('type', $type);

        $validator->addRule($node
                ? ('token_node_' . $idNode)
                : 'token_node', 'token:3600');

        /* Test des champs personnalisé de la node. */
        $canPublish = true;
        foreach ($fields as $value) {
            /* Si une node possède une relation requise, elle ne peut-être publié. */
            if ($value[ 'field_type' ] == 'one_to_many') {
                $rules = self::node()->getRules($value);

                if (empty($rules[ 'required' ])) {
                    continue;
                }

                /* Si la node existe. */
                if ($node) {
                    $oneToManyOption = OneToManyOption::createFromJson($value[ 'field_option' ]);

                    $entitys = self::query()
                        ->from($oneToManyOption->getRelationTable())
                        ->where($oneToManyOption->getForeignKey(), '=', $node[ 'entity_id' ])
                        ->limit(2)
                        ->fetchAll();

                    $canPublish = count($entitys) >= 1;
                } else {
                    /* Si la node n'existe pas et que les champs multiples sont requis, la node ne peut pas être publié. */
                    $canPublish = false;
                }
            } else {
                $validator
                    ->addRule($value[ 'field_name' ], $value[ 'field_rules' ])
                    ->addLabel($value[ 'field_name' ], t($value[ 'field_label' ]));
            }
        }

        if (!$validator->getInput('date', false)) {
            $validator->addInput('date', date('Y-m-d'));
        }
        if (!$validator->getInput('date_time', false)) {
            $validator->addInput('date_time', date('H:i'));
        }

        $validator
            ->addInput(
                'date_created',
                $validator->getInput('date') . ' ' . $validator->getInput('date_time')
            );

        if ($validator->getInput('node_status_id') == 1) {
            $validator->addRule(
                'date_created',
                'required|date_format:Y-m-d H:i|date_before_or_equal:' . date('Y-m-d H:i')
            );
        }

        /* Ne peut pas publier la node si les règles des relations ne sont pas respectées. */
        if (!$canPublish) {
            $validator->addRule('node_status_id', '!accepted');
        }

        return $validator;
    }

    private function getData(Validator $validator, ?string $type = null): array
    {
        /** @phpstan-var numeric|string $userIdInput */
        $userIdInput = $validator->getInput('user_id');

        $data = [
            'date_changed'     => (string) time(),
            'date_created'     => (string) strtotime($validator->getInputString('date_created')) ?: '',
            'meta_description' => $validator->getInput('meta_description'),
            'meta_noarchive'   => (bool) $validator->getInput('meta_noarchive'),
            'meta_nofollow'    => (bool) $validator->getInput('meta_nofollow'),
            'meta_noindex'     => (bool) $validator->getInput('meta_noindex'),
            'meta_title'       => $validator->getInput('meta_title'),
            'node_status_id'   => $validator->getInputInt('node_status_id'),
            'sticky'           => (bool) $validator->getInput('sticky'),
            'title'            => $validator->getInput('title'),
            'user_id'          => is_numeric($userIdInput)
                ? (int) $userIdInput
                : null
        ];

        if ($type !== null) {
            $data[ 'entity_id' ] = self::schema()->getIncrement('entity_' . $type);
            $data[ 'type' ]      = $type;
        }

        return $data;
    }

    private function getMeta(array $node, array $fields): array
    {
        if (!empty($node[ 'meta_description' ])) {
            $description = $node[ 'meta_description' ];
        } elseif (!empty($fields[ 'summary' ][ 'field_value' ])) {
            $description = $fields[ 'summary' ][ 'field_value' ];
        } elseif (!empty($fields[ 'body' ][ 'field_value' ])) {
            $description = $fields[ 'body' ][ 'field_value' ];
        } else {
            $description = self::config()->get('settings.meta_description');
        }

        /** @phpstan-var string $alias */
        $alias = self::alias()->getAlias('node/' . $node[ 'id' ], 'node/' . $node[ 'id' ]);

        $meta = [
            [
                'property' => 'og:title',
                'content'  => $node[ 'title' ]
            ], [
                'property' => 'og:type',
                'content'  => 'website'
            ], [
                'property' => 'og:description',
                'content'  => $this->cleanDescription($description)
            ], [
                'property' => 'og:site_name',
                'content'  => self::config()->get('settings.meta_title')
            ], [
                'property' => 'og:url',
                'content'  => self::router()->makeUrl('/' . ltrim($alias, '/'))
            ],
        ];

        $robots = '';
        if ($node[ 'meta_noindex' ]) {
            $robots .= 'noindex,';
        }
        if ($node[ 'meta_nofollow' ]) {
            $robots .= 'nofollow,';
        }
        if ($node[ 'meta_noarchive' ]) {
            $robots .= 'noarchive,';
        }

        if ($robots !== '') {
            $meta[] = [ 'name' => 'robots', 'content' => substr($robots, 0, -1) ];
        }

        if (!empty($fields[ 'image' ][ 'field_value' ])) {
            $meta[] = [ 'property' => 'og:image', 'content' => $fields[ 'image' ][ 'field_value' ] ];
        } elseif ($logo = self::config()->get('settings.logo')) {
            $meta[] = [ 'property' => 'og:image', 'content' => $logo ];
        }

        return $meta;
    }

    private function cleanDescription(string $str): string
    {
        $str = strip_tags($str);
        $str = htmlentities($str);
        $str = trim($str);
        $str = preg_replace('#[ \n\r\t\v\0]+#', ' ', $str) ?? '';

        return mb_strcut($str, 0, 200);
    }

    private function updateWeightEntity(array $field, array $data): void
    {
        $oneToManyOption = OneToManyOption::createFromJson($field[ 'field_option' ]);
        if ($oneToManyOption->getOrderBy() !== OneToManyOption::WEIGHT_FIELD) {
            return;
        }
        foreach ($data as $value) {
            self::query()
                ->update('entity_' . $field[ 'field_name' ], [
                    OneToManyOption::WEIGHT_FIELD => $value[ OneToManyOption::WEIGHT_FIELD ]
                ])
                ->where($field[ 'field_name' ] . '_id', '=', (int) $value[ 'id' ])
                ->execute();
        }
    }

    private function getListUsersId(): string
    {
        $usersId = self::query()->from('user')->lists('user_id');

        return implode(',', $usersId);
    }

    private function saveFile(array $node, string $nameField, Validator $validator): void
    {
        /** @phpstan-var UploadedFileInterface $uploadedFile */
        $uploadedFile = $validator->getInput($nameField);

        self::file()
            ->add($uploadedFile, $validator->getInputString("file-$nameField-name"))
            ->setName($nameField)
            ->withRandomPrefix()
            ->setPath("/node/{$node[ 'type' ]}/{$node[ 'id' ]}")
            ->isResolvePath()
            ->callGet(function (string $key) use ($node): ?string {
                $entityType = self::query()
                    ->from('entity_' . $node[ 'type' ])
                    ->where($node[ 'type' ] . '_id', '=', $node[ 'entity_id' ])
                    ->fetch();

                return isset($entityType[$key]) && is_string($entityType[ $key ])
                    ? $entityType[ $key ]
                    : null;
            })
            ->callMove(function (string $key, \SplFileInfo $fileInfo) use ($node): void {
                self::query()
                    ->update('entity_' . $node[ 'type' ], [ $key => $fileInfo->getPathname() ])
                    ->where($node[ 'type' ] . '_id', '=', $node[ 'entity_id' ])
                    ->execute();
            })
            ->callDelete(function (string $key) use ($node): void {
                self::query()
                    ->update('entity_' . $node[ 'type' ], [ $key => '' ])
                    ->where($node[ 'type' ] . '_id', '=', $node[ 'entity_id' ])
                    ->execute();
            })
            ->save();
    }
}
