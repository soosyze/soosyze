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
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
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

        $form = (new FormBuilder([
            'method'  => 'post',
            'action'  => self::router()->getRoute('node.store', [ ':type' => $type ]),
            'enctype' => 'multipart/form-data' ]))
            ->group('node-fieldset', 'fieldset', function ($form) use ($query, $content, $type) {
                $form->legend('node-title-legend', t('Fill in the following fields'))
                ->group('node-title-group', 'div', function ($form) use ($content) {
                    $form->label('node-title-label', t('Title of the content'))
                    ->text('title', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'required'    => 1,
                        'placeholder' => t('Title of the content'),
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
                        $form->label('node-' . $key . '-label', t($value[ 'field_label' ]));
                        switch ($value[ 'field_type' ]) {
                            case 'textarea':
                                $form->textarea($key, $content[ $key ], [
                                    'class'       => 'form-control editor',
                                    'required'    => $require,
                                    'rows'        => 8,
                                    'placeholder' => t('Enter your content here')
                                ]);

                                break;
                            default:
                                $type = $value[ 'field_type' ];
                                $form->$type($key, [
                                    'class'    => 'form-control',
                                    'required' => $require,
                                ]);

                                break;
                        }
                    }, [ 'class' => 'form-group' ]);
                }
            })
            ->group('node-seo-group', 'fieldset', function ($form) use ($content)
            {
                $form->legend('node-title-legend', t('SEO'))
                ->group('node-noindex-group', 'div', function ($form) use ($content)
                {
                    $form->checkbox('noindex', ['checked' => $content['noindex']])
                    ->label('node-noindex-label', '<span class="ui"></span> ' . t('Bloquer l\'indexation') . ' <code>noindex</code>', [
                        'for' => 'noindex'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('node-nofollow-group', 'div', function ($form) use ($content)
                {
                    $form->checkbox('nofollow', ['checked' => $content['nofollow']])
                    ->label('node-nofollow-label', '<span class="ui"></span> ' . t('Bloquer le suivi des liens') . ' <code>nofollow</code>', [
                        'for' => 'nofollow'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('node-noarchive-group', 'div', function ($form) use ($content)
                {
                    $form->checkbox('noarchive', ['checked' => $content['noarchive']])
                    ->label('node-noarchive-label', '<span class="ui"></span> ' . t('Bloquer la mise en cache') . ' <code>noarchive</code>', [
                        'for' => 'noarchive'
                    ]);
                }, [ 'class' => 'form-group' ]);
            }, [ 'class' => 'form-group' ])
            ->group('node-publish-group', 'div', function ($form) {
                $form->checkbox('published')
                ->label('node-publish-label', '<span class="ui"></span> ' . t('Publish content'), [
                    'for' => 'published'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token('token_node_create')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

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
                'title'             => 'required|string|max:255|htmlsc',
                'noindex'           => 'bool',
                'nofollow'          => 'bool',
                'noarchive'         => 'bool',
                'published'         => 'bool',
                'token_node_create' => 'token'
            ])
            ->setInputs($req->getParsedBody());
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
                'noindex'   => (bool) $validator->getInput('noindex'),
                'nofollow'  => (bool) $validator->getInput('nofollow'),
                'noarchive' => (bool) $validator->getInput('noarchive'),
                'published' => (bool) $validator->getInput('published'),
                'field'     => serialize($fields)
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
                ->make('page.content', 'node-show.php', $this->pathViews, [
                    'fields' => unserialize($node[ 'field' ])
                ])->override('page.content', [ 'node-show-' . $id . '.php', 'node-show-' . $node[ 'type' ] . '.php']);
        
        self::core()->callHook('node.show.tpl', [&$tpl, $node, $id]);

        if (!$node[ 'published' ]) {
            $tpl->view('page.messages', [
                'infos' => [ t('This content is not published') ]
            ]);
        }

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

        $form = (new FormBuilder([
            'method'  => 'post',
            'action'  => self::router()->getRoute('node.update', [ ':id' => $id ]),
            'enctype' => 'multipart/form-data' ]))
            ->group('node-fieldset', 'fieldset', function ($form) use ($query, $content, $id) {
                $form->legend('node-title-legend', t('Fill in the following fields'))
                ->group('node-title-group', 'div', function ($form) use ($content) {
                    $form->label('node-title-label', t('Title of the content'))
                    ->text('title', [
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
                        : unserialize($content[ 'field' ])[ $key ];

                    $form->group('node-' . $id . '-' . $key, 'div', function ($form) use ($value, $key, $content, $require) {
                        $form->label('node-' . $key . '-label', $value[ 'field_label' ]);
                        switch ($value[ 'field_type' ]) {
                            case 'textarea':
                                $form->textarea($key, $content[ $key ], [
                                    'class'       => 'form-control editor',
                                    'required'    => $require,
                                    'rows'        => 8,
                                    'placeholder' => t('Enter your content here')
                                ]);

                                break;
                            default:
                                $type = $value[ 'field_type' ];
                                $form->$type($key, [
                                    'class'    => 'form-control',
                                    'required' => $require,
                                ]);

                                break;
                        }
                    }, [ 'class' => 'form-group' ]);
                }
            })
            ->group('node-seo-group', 'fieldset', function ($form) use ($content)
            {
                $form->legend('node-title-legend', t('SEO'))
                ->group('node-noindex-group', 'div', function ($form) use ($content)
                {
                    $form->checkbox('noindex', ['checked' => $content['noindex']])
                    ->label('node-noindex-label', '<span class="ui"></span> ' . t('Bloquer l\'indexation') . ' <code>noindex</code>', [
                        'for' => 'noindex'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('node-nofollow-group', 'div', function ($form) use ($content)
                {
                    $form->checkbox('nofollow', ['checked' => $content['nofollow']])
                    ->label('node-nofollow-label', '<span class="ui"></span> ' . t('Bloquer le suivi des liens') . ' <code>nofollow</code>', [
                        'for' => 'nofollow'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('node-noarchive-group', 'div', function ($form) use ($content)
                {
                    $form->checkbox('noarchive', ['checked' => $content['noarchive']])
                    ->label('node-noarchive-label', '<span class="ui"></span> ' . t('Bloquer la mise en cache') . ' <code>noarchive</code>', [
                        'for' => 'noarchive'
                    ]);
                }, [ 'class' => 'form-group' ]);
            }, [ 'class' => 'form-group' ])
            ->group('node-publish-group', 'div', function ($form) use ($content) {
                $form->checkbox('published', [ 'checked' => $content[ 'published' ] ])
                ->label('node-publish-label', '<span class="ui"></span> ' . t('Publish content'), [
                    'for' => 'published'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token('token_node_edit')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

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
                'title'           => 'required|string|max:255|htmlsc',
                'noindex'         => 'bool',
                'nofollow'        => 'bool',
                'noarchive'       => 'bool',
                'published'       => 'bool',
                'token_node_edit' => 'token'
            ])
            ->setInputs($req->getParsedBody());
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
                'noindex'   => (bool) $validator->getInput('noindex'),
                'nofollow'  => (bool) $validator->getInput('nofollow'),
                'noarchive' => (bool) $validator->getInput('noarchive'),
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

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
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
