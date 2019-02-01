<?php

namespace Node\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

define('VIEWS_NODE', MODULES_CORE . 'Node' . DS . 'Views' . DS);
define('CONFIG_NODE', MODULES_CORE . 'Node' . DS . 'Config' . DS);

class Node extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = CONFIG_NODE . 'service.json';
        $this->pathRoutes   = CONFIG_NODE . 'routing.json';
    }

    public function admin()
    {
        $nodes = self::query()
            ->from('node')
            ->orderBy('changed', 'desc')
            ->fetchAll();

        foreach ($nodes as $key => $node) {
            $nodes[ $key ][ 'link_view' ]  = self::router()->getRoute('node.show', [
                ':item' => $node[ 'id' ] ]);
            $nodes[ $key ][ 'link_edit' ]  = self::router()->getRoute('node.edit', [
                ':item' => $node[ 'id' ] ]);
            $nodes[ $key ][ 'link_delet' ] = self::router()->getRoute('node.delete', [
                ':item' => $node[ 'id' ] ]);
        }

        $linkAdd = self::router()->getRoute('node.add');

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i>  Mes contenus'
                ])
                ->render('page.content', 'node-admin.php', VIEWS_NODE, [
                    'linkAdd' => $linkAdd,
                    'nodes'   => $nodes
        ]);
    }

    public function add($req)
    {
        $query = self::query()
            ->from('node_type')
            ->fetchAll();

        foreach ($query as $key => $node_type) {
            $query[ $key ][ 'link' ] = self::router()->getRoute('node.create', [
                ':item' => $node_type[ 'node_type' ] ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i> Ajouter du contenu'
                ])
                ->render('page.content', 'node-add.php', VIEWS_NODE, [
                    'node_type' => $query
        ]);
    }

    public function create($item, $req)
    {
        $query = self::query()
            ->from('node_type')
            ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $item)
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

        $action = self::router()->getRoute('node.store', [ ':item' => $item ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('node-fieldset', 'fieldset', function ($form) use ($query, $content, $item) {
                $form->legend('node-title-legend', 'Remplissiez les champs suivants')
                ->group('node-title-group', 'div', function ($form) use ($query, $content, $item) {
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

                    $form->group('node-' . $item . '-' . $key, 'div', function ($form) use ($value, $key, $content, $require) {
                        $form->label('node-' . $key . '-label', $key);
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
            ->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        $this->container->callHook('node.create.form', [ &$form, $content ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i> Ajouter du contenu de type ' . $item
                ])
                ->render('page.content', 'node-create.php', VIEWS_NODE, [
                    'form' => $form
        ]);
    }

    public function store($item, $req)
    {
        $query = self::query()
            ->from('node_type')
            ->leftJoin('node_type_field', 'node_type', 'node_type_field.node_type')
            ->leftJoin('field', 'field_id', 'field.field_id')
            ->where('node_type', $item)
            ->fetchAll();

        if (!$query) {
            return $this->get404($req);
        }

        $post = $req->getParsedBody();

        /* Ttest les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'title'     => 'required|string|max:255|htmlsc',
                'published' => 'bool',
                'token'     => 'token'
            ])
            ->setInputs($post);

        $this->container->callHook('node.store.validator', [ &$validator ]);

        /* Test des champs personnalisé de la node. */
        $validatorField = new Validator();
        foreach ($query as $value) {
            $key = $value[ 'field_name' ];
            $validatorField
                ->addRule($key, $value[ 'field_rules' ])
                ->addInput($key, $post[ $key ]);
        }

        $isValid      = $validator->isValid();
        $isValidField = $validatorField->isValid();

        if ($isValid && $isValidField) {
            /* Rassemble les champs personnalisés dans la node. */
            $node = [
                'title'     => $validator->getInput('title'),
                'type'      => $item,
                'created'   => (string) time(),
                'changed'   => (string) time(),
                'published' => (bool) $validator->getInput('published'),
                'field'     => serialize($validatorField->getInputs())
            ];

            $this->container->callHook('todo.store.before', [ $validator, &$node ]);
            self::query()
                ->insertInto('node', array_keys($node))
                ->values($node)
                ->execute();
            $this->container->callHook('node.store.after', [ $validator ]);

            $_SESSION[ 'success' ] = [ 'Votre contenu a été enregistrée.' ];
            $route                 = self::router()->getRoute('node.index');

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]      = array_merge(
            $validator->getInputs(),
            $validatorField->getInputs()
        );
        $_SESSION[ 'errors' ]      = array_merge(
            $validator->getErrors(),
            $validatorField->getErrors()
        );
        $_SESSION[ 'errors_keys' ] = array_merge(
            $validator->getKeyInputErrors(),
            $validatorField->getKeyInputErrors()
        );

        $route = self::router()->getRoute('node.create', [ ':item' => $item ]);
        new Redirect($route);
    }

    public function show($item, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $item)
            ->fetch();

        if (!$node) {
            return $this->get404($req);
        }

        $tpl = self::template()
                ->view('page', [
                    'title_main' => $node[ 'title' ],
                ])
                ->render('page.content', 'node-show.php', VIEWS_NODE, [
                    'fields' => unserialize($node[ 'field' ])
                ])->override('page.content', [ 'node-show-' . $item . '.php', 'node-show-' . $node['type'] ]);

        if (!$node[ 'published' ]) {
            if (!self::user()->isConnected()) {
                return $this->get404($req);
            } else {
                $tpl->view('page.messages', [
                    'infos' => [ 'Ce contenu n\'est pas publié !' ]
                ]);
            }
        }

        return $tpl;
    }

    public function edit($item, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $item)
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

        $this->container->callHook('node.edit.form.data', [ &$content, $item ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('node.update', [ ':item' => $item ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('node-fieldset', 'fieldset', function ($form) use ($query, $content, $item, $node) {
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

                    $form->group('node-' . $item . '-' . $key, 'div', function ($form) use ($value, $key, $content, $require) {
                        switch ($value[ 'field_type' ]) {
                            case 'textarea':
                                $form->label('label-' . $key, $key)
                                ->textarea($key, $key, $content[ $key ], [
                                    'class'    => 'form-control',
                                    'required' => $require,
                                    'rows'     => 8
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
            ->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        $this->container->callHook('node.edit.form', [ &$form, $content ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i> Modifier le contenu ' . $node[ 'title' ]
                ])
                ->render('page.content', 'node-edit.php', VIEWS_NODE, [ 'form' => $form ]);
    }

    public function update($item, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $item)
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
            ->fetchAll();

        /* Test les champs par defauts de la node. */
        $validator = (new Validator())
            ->setRules([
                'title'     => 'required|string|max:255|htmlsc',
                'published' => 'bool',
                'token'     => 'token'
            ])
            ->setInputs($post);

        $this->container->callHook('node.update.validator', [ &$validator, $item ]);

        /* Test les champs personnalisé de la node. */
        $validatorField = new Validator();
        foreach ($node_type as $value) {
            $key = $value[ 'field_name' ];
            $validatorField
                ->addRule($key, $value[ 'field_rules' ])
                ->addInput($key, $post[ $key ]);
        }

        $isValid      = $validator->isValid();
        $isValidField = $validatorField->isValid();

        if ($isValid && $isValidField) {
            $value = [
                'title'     => $validator->getInput('title'),
                'changed'   => (string) time(),
                'published' => (bool) $validator->getInput('published'),
                'field'     => serialize($validatorField->getInputs())
            ];

            $this->container->callHook('node.update.before', [ $validator, &$value,
                $item ]);
            self::query()
                ->update('node', $value)
                ->where('id', '==', $item)
                ->execute();
            $this->container->callHook('node.update.after', [ $validator, $item ]);

            $_SESSION[ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
        } else {
            $_SESSION[ 'inputs' ]      = array_merge(
                $validator->getInputs(),
                $validatorField->getInputs()
            );
            $_SESSION[ 'errors' ]      = array_merge(
                $validator->getErrors(),
                $validatorField->getErrors()
            );
            $_SESSION[ 'errors_keys' ] = array_merge(
                $validator->getKeyInputErrors(),
                $validatorField->getKeyInputErrors()
            );
        }

        $route = self::router()->getRoute('node.edit', [ ':item' => $item ]);

        return new Redirect($route);
    }

    public function delete($item, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $item)
            ->fetch();

        if (!$node) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'item' => 'required',
            ])
            ->setInputs([ 'item' => $item ]);

        $this->container->callHook('node.delete.validator', [ &$validator, $item ]);

        if ($validator->isValid()) {
            $this->container->callHook('node.delete.before', [ $validator, $item ]);
            self::query()
                ->from('node')
                ->delete()
                ->where('id', '==', $item)
                ->execute();
            $this->container->callHook('node.delete.after', [ $validator, $item ]);
        }

        $route = self::router()->getRoute('node.index');

        return new Redirect($route);
    }
}
