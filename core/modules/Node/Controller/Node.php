<?php

namespace SoosyzeCore\Node\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Node\Form\FormNode;

class Node extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin()
    {
        $nodes = self::query()
            ->from('node')
            ->orderBy('date_changed', 'desc')
            ->fetchAll();

        foreach ($nodes as &$node) {
            $node[ 'link_view' ]  = self::router()->getRoute('node.show', [
                ':id' => $node[ 'id' ] ]);
            $node[ 'link_edit' ]  = self::router()->getRoute('node.edit', [
                ':id' => $node[ 'id' ] ]);
            $node[ 'link_delete' ] = self::router()->getRoute('node.delete', [
                ':id' => $node[ 'id' ] ]);
        }

        $linkAdd = self::router()->getRoute('node.add');

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i> ' . t('My contents')
                ])
                ->make('page.content', 'node-admin.php', $this->pathViews, [
                    'linkAdd' => $linkAdd,
                    'nodes'   => $nodes
        ]);
    }

    public function add($req)
    {
        $query = self::query()
            ->from('node_type')
            ->fetchAll();

        foreach ($query as $key => &$value) {
            $req_granted = $req->withUri($req->getUri()->withQuery('node/add/' . $value[ 'node_type' ]));
            if (!self::core()->callHook('app.granted.route', [ $req_granted ])) {
                unset($query[ $key ]);
            }
            $value[ 'link' ] = self::router()->getRoute('node.create', [
                ':type' => $value[ 'node_type' ] ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i> ' . t('Add content')
                ])
                ->make('page.content', 'node-add.php', $this->pathViews, [
                    'node_type' => $query
        ]);
    }

    public function create($type, $req)
    {
        $query = self::query()
            ->from('node_type')
            ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $type)
            ->orderBy('field_weight', 'asc')
            ->fetchAll();

        if (!$query) {
            return $this->get404($req);
        }

        $content = [ 'title' => '', 'published' => '', 'noindex' => '', 'nofollow' => '', 'noarchive' => '' ];

        $this->container->callHook('node.create.form.data', [ &$content ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormNode([
            'method'  => 'post',
            'action'  => self::router()->getRoute('node.store', [ ':type' => $type ]),
            'enctype' => 'multipart/form-data' ], self::file()))
            ->content($content, $type, $query)
            ->make();

        $this->container->callHook('node.create.form', [ &$form, $content ]);

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
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i> ' . t('Add content of type :name', [':name' => $type])
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-create.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($type, $req)
    {
        $query = self::query()
            ->from('node_type')
            ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $type)
            ->orderBy('field_weight')
            ->fetchAll();

        if (!$query) {
            return $this->get404($req);
        }

        /* Test les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'meta_noarchive'   => 'bool',
                'meta_nofollow'    => 'bool',
                'meta_noindex'     => 'bool',
                'published'        => 'bool',
                'title'            => 'required|string|max:255|htmlsc',
                'token_node'       => 'token'
            ])
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());

        /* Test des champs personnalisés de la node. */
        $files  = [];
        foreach ($query as $value) {
            $key = $value[ 'field_name' ];
            $validator->addRule($key, $value[ 'field_rules' ]);
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $key;
            }
        }

        $this->container->callHook('node.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            /* Prépare les champs de la table enfant. */
            $fields = [];
            foreach ($query as $value) {
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    unset($fields[ $key ]);
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fields[ $key ] = implode(',', $validator->getInput($key, []));
                } else {
                    $fields[ $key ] = $validator->getInput($key, '');
                }
            }
            self::query()
                ->insertInto('entity_' . $type, array_keys($fields))
                ->values($fields)
                ->execute();
            /* Télécharge et enregistre les fichiers. */
            foreach ($query as $value) {
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    $id = self::schema()->getIncrement('entity_' . $type);
                    $this->saveFile($type, $id, $value[ 'field_name' ], $validator);
                }
            }
            /* Rassemble les champs personnalisés dans la node. */
            $node = [
                'date_changed'     => (string) time(),
                'date_created'     => (string) time(),
                'entity_id'        => self::schema()->getIncrement('entity_' . $type),
                'meta_noarchive'   => (bool) $validator->getInput('meta_noarchive'),
                'meta_nofollow'    => (bool) $validator->getInput('meta_nofollow'),
                'meta_noindex'     => (bool) $validator->getInput('meta_noindex'),
                'published'        => (bool) $validator->getInput('published'),
                'title'            => $validator->getInput('title'),
                'type'             => $type,
            ];

            $this->container->callHook('todo.store.before', [ $validator, &$node ]);
            self::query()
                ->insertInto('node', array_keys($node))
                ->values($node)
                ->execute();
            $this->container->callHook('node.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Your content has been saved.') ];
            $route                               = self::router()->getRoute('node.index');

            return new Redirect($route);
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($files);
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        $route = self::router()->getRoute('node.create', [ ':type' => $type ]);

        return new Redirect($route);
    }

    public function show($id, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $id)
            ->fetch();

        if (!$node) {
            return $this->get404($req);
        }
        $fields = self::node()->makeFieldsById($node['type'], $node['entity_id']);

        $tpl = self::template()
                ->view('page', [
                    'title_main' => $node[ 'title' ],
                ])
                ->make('page.content', 'node-show.php', $this->pathViews, [
                    'fields' => $fields
                ])->override('page.content', [ 'node-show-' . $id . '.php', 'node-show-' . $node[ 'type' ] . '.php']);
        
        self::core()->callHook('node.show.tpl', [&$tpl, $node, $id]);

        return $tpl;
    }

    public function edit($id, $req)
    {
        $content = self::query()
            ->from('node')
            ->where('id', '==', $id)
            ->fetch();

        if (!$content) {
            return $this->get404($req);
        }
        
        $content += self::query()
            ->from('entity_' . $content[ 'type' ])
            ->where($content[ 'type' ] . '_id', '==', $content[ 'id' ])
            ->fetch();

        $query = self::query()
            ->from('node_type')
            ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $content[ 'type' ])
            ->orderBy('field_weight', 'asc')
            ->fetchAll();

        if (!$query) {
            return $this->get404($req);
        }

        $this->container->callHook('node.edit.form.data', [ &$content, $id ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormNode([
            'method'  => 'post',
            'action'  => self::router()->getRoute('node.edit', [ ':id' => $id ]),
            'enctype' => 'multipart/form-data' ], self::file()))
            ->content($content, $content['type'], $query)
            ->make();

        $this->container->callHook('node.edit.form', [ &$form, $content ]);

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
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i> ' . t('Edit :title content', [':title' => $content[ 'title' ]])
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'node-edit.php', $this->pathViews, [ 'form' => $form ]);
    }

    public function update($id, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $id)
            ->fetch();

        if (!$node) {
            return $this->get404($req);
        }

        $node_type = self::query()
            ->from('node_type')
            ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $node[ 'type' ])
            ->orderBy('field_weight')
            ->fetchAll();

        /* Test les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'meta_noarchive'   => 'bool',
                'meta_nofollow'    => 'bool',
                'meta_noindex'     => 'bool',
                'published'        => 'bool',
                'title'            => 'required|string|max:255|htmlsc',
                'token_node'       => 'token'
            ])
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());
        /* Test des champs personnalisé de la node. */
        $files  = [];
        foreach ($node_type as $value) {
            if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                $files[] = $value[ 'field_type' ];
            }
            $validator->addRule($value[ 'field_name' ], $value[ 'field_rules' ]);
        }
        $this->container->callHook('node.update.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $fields = [];
            foreach ($node_type as $value) {
                $key = $value[ 'field_name' ];
                if (in_array($value[ 'field_type' ], [ 'image', 'file' ])) {
                    unset($fields[ $key ]);
                    $this->saveFile($node[ 'type' ], $id, $key, $validator);
                } elseif ($value[ 'field_type' ] === 'checkbox') {
                    $fields[ $key ] = implode(',', $validator->getInput($key, []));
                } else {
                    $fields[ $key ] = $validator->getInput($key, '');
                }
            }

            $value = [
                'date_changed'     => (string) time(),
                'meta_noarchive'   => (bool) $validator->getInput('meta_noarchive'),
                'meta_nofollow'    => (bool) $validator->getInput('meta_nofollow'),
                'meta_noindex'     => (bool) $validator->getInput('meta_noindex'),
                'published'        => (bool) $validator->getInput('published'),
                'title'            => $validator->getInput('title')
            ];

            $this->container->callHook('node.update.before', [ $validator, &$value,
                $id ]);
            self::query()
                ->update('node', $value)
                ->where('id', '==', $id)
                ->execute();
            self::query()
                ->update('entity_' . $node[ 'type' ], $fields)
                ->where($node[ 'type' ] . '_id', '==', $id)
                ->execute();
            $this->container->callHook('node.update.after', [ $validator, $id ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($files);
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        $route = self::router()->getRoute('node.edit', [ ':id' => $id ]);

        return new Redirect($route);
    }

    public function delete($id, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $id)
            ->fetch();

        if (!$node) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'id' => 'required',
            ])
            ->setInputs([ 'id' => $id ]);

        $this->container->callHook('node.delete.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('node.delete.before', [ $validator, $id ]);
            self::query()
                ->from('node')
                ->delete()
                ->where('id', '==', $id)
                ->execute();
            $this->container->callHook('node.delete.after', [ $validator, $id ]);
        }

        $route = self::router()->getRoute('node.index');

        return new Redirect($route);
    }
    
    private function saveFile($table, $id, $name_field, $validator)
    {
        $dir = self::core()->getSettingEnv('files_public', 'app/files') . "/node/$id";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        self::file()
            ->add($validator->getInput($name_field), $validator->getInput("file-name-$name_field"))
            ->setName($name_field)
            ->setPath($dir)
            ->setResolvePath()
            ->callGet(function ($key, $name) use ($id, $table) {
                return self::query()->from('entity_' . $table)->where($table . '_id', '==', $id)->fetch()[ $key ];
            })
            ->callMove(function ($key, $name, $move) use ($id, $table) {
                self::query()->update('entity_' . $table, [ $key => $move ])->where($table . '_id', '==', $id)->execute();
            })
            ->callDelete(function ($key, $name) use ($id, $table) {
                self::query()->update('entity_' . $table, [ $key => '' ])->where($table . '_id', '==', $id)->execute();
            })
            ->save();
    }
}
