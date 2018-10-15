<?php

namespace Node\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use Soosyze\Components\Http\Redirect;

define("VIEWS_NODE", MODULES_CORE . 'Node' . DS . 'Views' . DS);
define("CONFIG_NODE", MODULES_CORE . 'Node' . DS . 'Config' . DS);

class Node extends \Soosyze\Controller
{
    protected $pathServices = CONFIG_NODE . 'service.json';

    protected $pathRoutes = CONFIG_NODE . 'routing.json';

    public function addView($r)
    {
        $query = self::query()
            ->from('node_type')
            ->fetchAll();

        foreach ($query as $key => $node_type) {
            $query[ $key ][ 'link' ] = self::router()->getRoute('node.add.item', [
                ':item' => $node_type[ 'node_type' ] ]);
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i> Ajouter du contenu'
                ])
                ->render('page.content', 'page-node-add-view.php', VIEWS_NODE, [
                    'node_type' => $query
        ]);
    }

    public function addItem($item, $req)
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
        if (isset($_SESSION[ 'inputs' ])) {
            $content = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('node.add.item.check', [ ':item' => $item ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('fieldset-main', 'fieldset', function ($form) use ($query, $content, $item) {
                $form->legend('legend-information', 'Remplissiez les champs suivants')
                ->group('group-title', 'div', function ($form) use ($query, $content, $item) {
                    $form->label('labelTitle', 'Titre du contenu')
                    ->text('title', 'title', [
                        'class'       => 'form-control',
                        'value'       => $content[ 'title' ],
                        'required'    => 1,
                        'placeholder' => 'Titre du contenu',
                    ]);
                }, [ 'class' => 'form-group' ]);

                foreach ($query as $value) {
                    $key     = $value[ 'field_name' ];
                    $rules   = $value[ 'field_rules' ];
                    $require = (new Validator())->addRule($key, $rules)->isRequire($key);

                    /* Si le contenu du champ n'existe pas alors il est déclaré vide. */
                    $content[ $key ] = isset($content[ $key ])
                        ? $content[ $key ]
                        : '';

                    $form->group('node-' . $item . '-' . $key, 'div', function ($form) use ($value, $key, $content, $require) {
                        $form->label('label-' . $key, $key);
                        switch ($value[ 'field_type' ]) {
                            case 'textarea':
                                $form->textarea($key, $key, $content[ $key ], [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Entrer votre contenu içi...',
                                    'rows'        => 8,
                                    'required'    => $require
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
                    }, [ 'class' => "form-group" ]);
                }

                $form->group('publish', 'div', function ($form) {
                    $form->checkbox('published', 'published')
                    ->label('labelPublished', '<span class="ui"></span> Publier le contenu', [
                        'for' => 'published' ]);
                }, [ 'class' => "form-group" ])
                ->token();
            })
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        $reponse = self::template()
            ->setTheme()
            ->view('page', [
                'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i> Ajouter du contenu de type ' . $item
            ])
            ->render('page.content', 'page-node-add-item.php', VIEWS_NODE, [
            'form' => $form
        ]);

        $this->container->callHook('node.add.item.after', [ &$r, &$reponse ]);

        return $reponse;
    }

    public function addItemCheck($item, $req)
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
                'published' => 'bool|htmlsc',
                'token'     => 'token'
            ])
            ->setInputs($post);

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
                $validator->getInput('title'),
                $item,
                ( string ) time(),
                ( string ) time(),
                ( bool ) $validator->getInput('published'),
                serialize($validatorField->getInputs())
            ];

            self::query()
                ->insertInto('node', [ 'title', 'type', 'created', 'changed',
                    'published', 'field' ])
                ->values($node)
                ->execute();

            $_SESSION[ 'success' ] = [ 'Votre contenu a été enregistrée.' ];
            $route                 = self::router()->getRoute('node.view.all');

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ] = array_merge(
            $validator->getInputs(),
            $validatorField->getInputs()
        );
        $_SESSION[ 'errors' ] = array_merge(
            $validator->getErrors(),
            $validatorField->getErrors()
        );
        $_SESSION[ 'errors_keys' ] = array_merge(
            $validator->getKeyUniqueErrors(),
            $validatorField->getKeyUniqueErrors()
        );

        $route = self::router()->getRoute('node.add.item', [ ':item' => $item ]);
        new Redirect($route);
    }

    public function view($item, $req)
    {
        $node = self::query()
            ->from('node')
            ->where('id', '==', $item)
            ->fetch();

        if (!$node) {
            return $this->get404($req);
        }

        $tpl = self::template()
            ->setTheme(false)
            ->view('page', [
                'title_main' => $node[ 'title' ],
            ])
            ->render('page.content', 'node.php', VIEWS_NODE, [
            'fields' => unserialize($node[ 'field' ])
        ]);

        if (!$node[ 'published' ] && !self::user()->isGranted('node.views.notpublished')) {
            return $this->get404($req);
        } else {
            $tpl->view('page', [
                'messages' => [ 'info' => [ 'Ce contenu n\'est pas publié !' ] ]
            ]);
        }

        return $tpl;
    }

    public function views()
    {
        $nodes = self::query()
            ->from('node')
            ->orderBy('changed', 'desc')
            ->fetchAll();

        foreach ($nodes as $key => $node) {
            $nodes[ $key ][ 'link_view' ]  = self::router()->getRoute('node.view', [
                ':item' => $node[ 'id' ] ]);
            $nodes[ $key ][ 'link_edit' ]  = self::router()->getRoute('node.edit', [
                ':item' => $node[ 'id' ] ]);
            $nodes[ $key ][ 'link_delet' ] = self::router()->getRoute('node.delete', [
                ':item' => $node[ 'id' ] ]);
        }

        $linkAdd = self::router()->getRoute('node.add.view');

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i>  Mes contenus'
                ])
                ->render('page.content', 'page-node-views.php', VIEWS_NODE, [
                    'linkAdd' => $linkAdd,
                    'nodes'   => $nodes
        ]);
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
        if (isset($_SESSION[ 'inputs' ])) {
            $content = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('node.edit.check', [ ':item' => $item ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('fieldset-main', 'fieldset', function ($form) use ($query, $content, $item, $node) {
                $form->legend('legend-information', 'Remplissiez les champs suivants')
                ->group('group-title', 'div', function ($form) use ($content) {
                    $form->label('labelTitle', 'Titre du contenu')
                    ->text('title', 'title', [
                        'class'    => 'form-control', 'value'    => $content[ 'title' ],
                        'required' => 1
                    ]);
                }, [ 'class' => 'form-group' ]);

                foreach ($query as $value) {
                    $key     = $value[ 'field_name' ];
                    $rules   = $value[ 'field_rules' ];
                    $require = (new Validator())->addRule($key, $rules)->isRequire($key);

                    /* Si le contenu du champs n'existe pas alors il est déclaré vide. */
                    $content[ $key ] = isset($content[ $key ])
                        ? $content[ $key ]
                        : unserialize($node[ 'field' ])[ $key ];

                    $form->group('node-' . $item . '-' . $key, 'div', function ($form) use ($value, $key, $content, $require) {
                        switch ($value[ 'field_type' ]) {
                            case 'textarea':
                                $form->label('label-' . $key, $key, [ 'for' => $key ])
                                ->textarea($key, $key, $content[ $key ], [
                                    'class'    => 'form-control',
                                    'rows'     => 8,
                                    'required' => $require,
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
                $form->group('publish', 'div', function ($form) use ($content) {
                    $form->checkbox('published', 'published', [ 'checked' => $content[ 'published' ]])
                    ->label('labelPublished', '<span class="ui"></span> Publier le contenu', [
                        'for' => 'published' ]);
                }, [ 'class' => 'form-group' ])
                ->token();
            })
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        $reponse = self::template()
            ->setTheme()
            ->view('page', [
                'title_main' => '<i class="glyphicon glyphicon-file" aria-hidden="true"></i> Modifier le contenu ' . $node[ 'title' ]
            ])
            ->render(
                'page.content',
                'page-node-add-item.php',
                VIEWS_NODE,
                [ 'form' => $form ]
        );

        $this->container->callHook('node.edit.item.after', [ &$r, &$reponse ]);

        return $reponse;
    }

    public function editCheck($item, $req)
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
                'published' => 'bool|htmlsc',
                'token'     => 'token'
            ])
            ->setInputs($post);

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
            self::query()
                ->update('node', [
                    'title'     => $validator->getInput('title'),
                    'changed'   => ( string ) time(),
                    'published' => ( bool ) $validator->getInput('published'),
                    'field'     => serialize($validatorField->getInputs())
                ])
                ->where('id', '==', $item)
                ->execute();
            $_SESSION[ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
        } else {
            $_SESSION[ 'inputs' ] = array_merge(
                $validator->getInputs(),
                $validatorField->getInputs()
            );
            $_SESSION[ 'errors' ] = array_merge(
                $validator->getErrors(),
                $validatorField->getErrors()
            );
            $_SESSION[ 'errors_keys' ] = array_merge(
                $validator->getKeyUniqueErrors(),
                $validatorField->getKeyUniqueErrors()
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

        if ($validator->isValid()) {
            self::query()
                ->from('node')
                ->delete()
                ->where('id', '==', $item)
                ->execute();
        }

        $route = self::router()->getRoute('node.view.all');

        return new Redirect($route);
    }
}
