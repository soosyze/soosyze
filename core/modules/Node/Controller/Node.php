<?php

namespace SoosyzeCore\Node\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

class Node extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin()
    {
        $nodes = self::query()
            ->from('node')
            ->orderBy('changed', 'desc')
            ->fetchAll();

        foreach ($nodes as &$node) {
            $node[ 'link_view' ]  = self::router()->getRoute('node.show', [
                ':id' => $node[ 'id' ] ]);
            $node[ 'link_edit' ]  = self::router()->getRoute('node.edit', [
                ':id' => $node[ 'id' ] ]);
            $node[ 'link_delet' ] = self::router()->getRoute('node.delete', [
                ':id' => $node[ 'id' ] ]);
        }

        $linkAdd = self::router()->getRoute('node.add');

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i>  Mes contenus'
                ])
                ->render('page.content', 'node-admin.php', $this->pathViews, [
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
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i> Ajouter du contenu'
                ])
                ->render('page.content', 'node-add.php', $this->pathViews, [
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

        $content = [ 'title' => '', 'published' => '' ];

        $this->container->callHook('node.create.form.data', [ &$content ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormBuilder([
            'method'  => 'post',
            'action'  => self::router()->getRoute('node.store', [ ':type' => $type ]),
            'enctype' => 'multipart/form-data' ]))
            ->group('node-fieldset', 'fieldset', function ($form) use ($query, $content, $type) {
                $form->legend('node-title-legend', 'Remplissiez les champs suivants')
                ->group('node-title-group', 'div', function ($form) use ($content) {
                    $form->label('node-title-label', 'Titre du contenu')
                    ->text('title', 'title', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'required'    => 1,
                        'placeholder' => 'Titre du contenu',
                        'value'       => $content[ 'title' ]
                    ]);
                }, [ 'class' => 'form-group' ]);

                foreach ($query as $value) {
                    $key     = $value[ 'field_name' ];
                    $rules   = $value[ 'field_rules' ];
                    $require = (new Validator())->addRule($key, $rules)->isRequired($key);

                    /* Si le contenu du champ n'existe pas alors il est déclaré vide. */
                    $content[ $key ] = isset($content[ $key ])
                        ? $content[ $key ]
                        : '';

                    $form->group('node-' . $type . '-' . $key, 'div', function ($form) use ($value, $key, $content, $require) {
                        $form->label('node-' . $key . '-label', $value[ 'field_label' ]);
                        switch ($value[ 'field_type' ]) {
                            case 'textarea':
                                $form->textarea($key, $key, $content[ $key ], [
                                    'class'       => 'form-control',
                                    'required'    => $require,
                                    'rows'        => 8,
                                    'placeholder' => 'Entrer votre contenu içi...'
                                ]);

                                break;
                            default:
                                $type = $value[ 'field_type' ];
                                $form->$type($key, $key, [
                                    'class'    => 'form-control',
                                    'required' => $require,
                                ]);

                                break;
                        }
                    }, [ 'class' => 'form-group' ]);
                }
            })
            ->group('node-publish-group', 'div', function ($form) {
                $form->checkbox('published', 'published')
                ->label('node-publish-label', '<span class="ui"></span> Publier le contenu', [
                    'for' => 'published'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token('token_node_create')
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

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
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i> Ajouter du contenu de type ' . $type
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'node-create.php', $this->pathViews, [
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

        $post = $req->getParsedBody();

        /* Ttest les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'title'             => 'required|string|max:255|htmlsc',
                'published'         => 'bool',
                'token_node_create' => 'token'
            ])
            ->setInputs($post);
        /* Test des champs personnalisé de la node. */
        foreach ($query as $value) {
            $validator->addRule($value[ 'field_name' ], $value[ 'field_rules' ]);
            $fields[ $value[ 'field_name' ] ] = $validator->hasInput($value[ 'field_name' ])
                ? $validator->getInput($value[ 'field_name' ])
                : null;
        }

        $this->container->callHook('node.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            /* Rassemble les champs personnalisés dans la node. */
            $node = [
                'title'     => $validator->getInput('title'),
                'type'      => $type,
                'created'   => (string) time(),
                'changed'   => (string) time(),
                'published' => (bool) $validator->getInput('published'),
                'field'     => serialize($fields)
            ];

            $this->container->callHook('todo.store.before', [ $validator, &$node ]);
            self::query()
                ->insertInto('node', array_keys($node))
                ->values($node)
                ->execute();
            $this->container->callHook('node.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ 'Votre contenu a été enregistrée.' ];
            $route                               = self::router()->getRoute('node.index');

            return new Redirect($route);
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputs();
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

        $tpl = self::template()
                ->view('page', [
                    'title_main' => $node[ 'title' ],
                ])
                ->render('page.content', 'node-show.php', $this->pathViews, [
                    'fields' => unserialize($node[ 'field' ])
                ])->override('page.content', [ 'node-show-' . $id . '.php', 'node-show-' . $node[ 'type' ] ]);

        if (!$node[ 'published' ]) {
            $tpl->view('page.messages', [
                'infos' => [ 'Ce contenu n\'est pas publié !' ]
            ]);
        }

        return $tpl;
    }

    public function edit($id, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $id)
            ->fetch();

        if (!$node) {
            return $this->get404($req);
        }

        $query = self::query()
            ->from('node_type')
            ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $node[ 'type' ])
            ->orderBy('field_weight', 'asc')
            ->fetchAll();

        if (!$query) {
            return $this->get404($req);
        }

        $content = [ 'title' => $node[ 'title' ], 'published' => $node[ 'published' ] ];

        $this->container->callHook('node.edit.form.data', [ &$content, $id ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormBuilder([
            'method'  => 'post',
            'action'  => self::router()->getRoute('node.update', [ ':id' => $id ]),
            'enctype' => 'multipart/form-data' ]))
            ->group('node-fieldset', 'fieldset', function ($form) use ($query, $content, $id, $node) {
                $form->legend('node-title-legend', 'Remplissiez les champs suivants')
                ->group('node-title-group', 'div', function ($form) use ($content) {
                    $form->label('node-title-label', 'Titre du contenu')
                    ->text('title', 'title', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'required'  => 1,
                        'rows'      => 8,
                        'value'     => $content[ 'title' ]
                    ]);
                }, [ 'class' => 'form-group' ]);

                foreach ($query as $value) {
                    $key     = $value[ 'field_name' ];
                    $rules   = $value[ 'field_rules' ];
                    $require = (new Validator())->addRule($key, $rules)->isRequired($key);

                    /* Si le contenu du champs n'existe pas alors il est déclaré vide. */
                    $content[ $key ] = isset($content[ $key ])
                        ? $content[ $key ]
                        : unserialize($node[ 'field' ])[ $key ];

                    $form->group('node-' . $id . '-' . $key, 'div', function ($form) use ($value, $key, $content, $require) {
                        $form->label('node-' . $key . '-label', $value[ 'field_label' ]);
                        switch ($value[ 'field_type' ]) {
                            case 'textarea':
                                $form->textarea($key, $key, $content[ $key ], [
                                    'class'       => 'form-control',
                                    'required'    => $require,
                                    'rows'        => 8,
                                    'placeholder' => 'Entrer votre contenu içi...'
                                ]);

                                break;
                            default:
                                $type = $value[ 'field_type' ];
                                $form->$type($key, 'node-' . $key, [
                                    'class'    => 'form-control',
                                    'required' => $require,
                                ]);

                                break;
                        }
                    }, [ 'class' => 'form-group' ]);
                }
            })
            ->group('node-publish-group', 'div', function ($form) use ($content) {
                $form->checkbox('published', 'published', [ 'checked' => $content[ 'published' ] ])
                ->label('node-publish-label', '<span class="ui"></span> Publier le contenu', [
                    'for' => 'published'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token('token_node_edit')
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

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
                    'title_main' => '<i class="fa fa-file" aria-hidden="true"></i> Modifier le contenu ' . $node[ 'title' ]
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'node-edit.php', $this->pathViews, [ 'form' => $form ]);
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

        $post = $req->getParsedBody();

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
                'title'           => 'required|string|max:255|htmlsc',
                'published'       => 'bool',
                'token_node_edit' => 'token'
            ])
            ->setInputs($post);
        /* Test des champs personnalisé de la node. */
        foreach ($node_type as $value) {
            $validator->addRule($value[ 'field_name' ], $value[ 'field_rules' ]);
            $fields[ $value[ 'field_name' ] ] = $validator->hasInput($value[ 'field_name' ])
                ? $validator->getInput($value[ 'field_name' ])
                : null;
        }

        $this->container->callHook('node.update.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $value = [
                'title'     => $validator->getInput('title'),
                'changed'   => (string) time(),
                'published' => (bool) $validator->getInput('published'),
                'field'     => serialize($fields)
            ];

            $this->container->callHook('node.update.before', [ $validator, &$value,
                $id ]);
            self::query()
                ->update('node', $value)
                ->where('id', '==', $id)
                ->execute();
            $this->container->callHook('node.update.after', [ $validator, $id ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
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
}
