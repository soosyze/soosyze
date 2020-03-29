<?php

namespace SoosyzeCore\Block\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;

class Block extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function show($id, $req)
    {
        if (!($block = self::query()->from('block')->where('block_id', '==', $id)->fetch())) {
            return $this->get404($req);
        }

        $block[ 'link_edit' ]   = self::router()->getRoute('block.edit', [ ':id' => $id ]);
        $block[ 'link_delete' ] = self::router()->getRoute('block.delete', [ ':id' => $id ]);
        $block[ 'link_update' ] = self::router()->getRoute('block.update', [ ':id' => $id ]);

        if (!empty($block[ 'hook' ])) {
            $data               = self::block()->getBlocks();
            $tpl                = self::template()->createBlock($data[ $block[ 'key_block' ] ][ 'tpl' ], $data[ $block[ 'key_block' ] ][ 'path' ]);
            $block[ 'content' ] .= (string) self::core()->callHook(
                'block.' . $block[ 'hook' ],
                [
                    $tpl,
                    empty($block[ 'options' ])
                        ? []
                        : json_decode($block[ 'options' ], true)
                ]
            );
        }

        return self::template()
                ->createBlock('block-show.php', $this->pathViews)
                ->addVars([ 'block' => $block ]);
    }

    public function create($section)
    {
        $data = self::block()->getBlocks();

        $form = new FormBuilder([
            'method' => 'POST',
            'action' => self::router()->getRoute('block.store', [ ':section' => $section ])
        ]);
        foreach ($data as $key => &$block) {
            if (!empty($block[ 'hook' ])) {
                $tpl     = self::template()->createBlock($block[ 'tpl' ], $block[ 'path' ]);
                $content = self::core()->callHook('block.' . $block[ 'hook' ], [
                    $tpl, empty($block[ 'options' ])
                        ? []
                        : $block[ 'options' ]
                ]);
            } else {
                $content = self::template()
                        ->createBlock($block[ 'tpl' ], $block[ 'path' ])
                        ->addVars([
                            'src_image' => self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets/static.svg'
                ]);
            }

            $form->group('type_block_' . $key . '-group', 'div', function ($form) use ($key, $content) {
                $form->radio('type_block', [
                        'id'    => "type_block-$key",
                        'value' => $key
                    ])
                    ->html('type_block-label', '<div:attr>:_content</div>', [
                        'class'    => 'block-content',
                        '_content' => $content
                ]);
            });
        }
        $form->token("token_$section")
            ->submit('submit', t('Add'), [ 'class' => 'btn btn-success' ]);

        $this->container->callHook('block.create.form', [ &$form, $data ]);

        return self::template()
                ->createBlock('block-create.php', $this->pathViews)
                ->addVars([
                    'section' => $section,
                    'blocks'  => $data,
                    'form'    => $form
        ]);
    }

    public function store($section, $req)
    {
        $blocks = self::block()->getBlocks();

        $validator = (new Validator())
            ->setRules([
                'type_block'     => 'required|string|max:255',
                "token_$section" => 'token'
            ])
            ->setInputs($req->getParsedBody());

        $this->container->callHook('block.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $type    = $validator->getInput('type_block');
            $hook    = null;
            $content = '';
            if (empty($blocks[ $type ][ 'hook' ])) {
                $content = (string) self::template()
                        ->createBlock($blocks[ $type ][ 'tpl' ], $blocks[ $type ][ 'path' ])
                        ->addVars([
                            'src_image' => self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets/static.svg'
                ]);
            } else {
                $hook = $blocks[ $type ][ 'hook' ];
            }
            $values = [
                'section'          => $section,
                'title'            => $blocks[ $type ][ 'title' ],
                'content'          => $content,
                'weight'           => 1,
                'visibility_roles' => true,
                'roles'            => '1,2',
                'hook'             => $hook,
                'key_block'        => $type,
                'options'          => empty($blocks[ $type ][ 'options' ])
                    ? null
                    : json_encode($blocks[ $type ][ 'options' ])
            ];
            $this->container->callHook('block.store.before', [ $validator, &$values ]);
            self::query()
                ->insertInto('block', array_keys($values))
                ->values($values)
                ->execute();
            $this->container->callHook('block.store.after', [ $validator, $values ]);
        }
        $route = self::router()->getRoute('section.admin', [ ':theme' => 'theme' ]);

        return new \Soosyze\Components\Http\Redirect($route);
    }

    public function edit($id, $req)
    {
        $data            = self::query()->from('block')->where('block_id', '==', $id)->fetch();
        $data[ 'roles' ] = explode(',', $data[ 'roles' ]);

        $this->container->callHook('block.edit.form.data', [ &$data ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('block.update', [ ':id' => $data[ 'block_id' ] ]);
        $form   = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('block-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('block-legend', t('Edit block'))
                ->group('title-group', 'div', function ($form) use ($data) {
                    $form->label('title-label', t('Title'))
                    ->text('title', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'Titre',
                        'required'    => 1,
                        'value'       => $data[ 'title' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('content-group', 'div', function ($form) use ($data) {
                    $form->label('content-label', t('Content'), [
                        'for' => 'content'
                    ])
                    ->textarea('content', $data[ 'content' ], [
                        'class'       => 'form-control editor',
                        'placeholder' => '<p>Hello World!</p>',
                        'required'    => 1,
                        'rows'        => 8
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('class-group', 'div', function ($form) use ($data) {
                    $form->label('class-label', t('Class CSS'))
                    ->text('class', [
                        'class'       => 'form-control',
                        'placeholder' => 'text-beautiful',
                        'value'       => $data[ 'class' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('page-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('page-legend', t('Visibility by pages'))
                ->group('visibility_pages_1-group', 'div', function ($form) use ($data) {
                    $form->radio('visibility_pages', [
                        'checked'  => !$data[ 'visibility_pages' ],
                        'id'       => 'visibility_pages_1',
                        'required' => 1,
                        'value'    => 0
                    ])->label('visibility_pages-label', t('Hide the block on the pages listed'), [
                        'for' => 'visibility_pages_1'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('visibility_pages_2-group', 'div', function ($form) use ($data) {
                    $form->radio('visibility_pages', [
                        'checked'  => $data[ 'visibility_pages' ],
                        'id'       => 'visibility_pages_2',
                        'required' => 1,
                        'value'    => 1
                    ])->label('visibility_pages-label', t('Display the block on the pages listed'), [
                        'for' => 'visibility_pages_2'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('pages-group', 'div', function ($form) use ($data) {
                    $form->label('pages-label', t('List of pages'), [
                        'data-tooltip' => t('Enter a path by line. The "%" character is a wildcard character that specifies all characters.')
                    ])
                    ->textarea('pages', $data[ 'pages' ], [
                        'class'       => 'form-control',
                        'placeholder' => 'admin' . PHP_EOL . 'admin/*',
                        'rows'        => 5
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('roles-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('roles-legend', t('Visibility by roles'))
                ->group('visibility_roles_1-group', 'div', function ($form) use ($data) {
                    $form->radio('visibility_roles', [
                        'checked'  => !$data[ 'visibility_roles' ],
                        'id'       => 'visibility_roles_1',
                        'required' => 1,
                        'value'    => 0
                    ])->label('visibility_roles-label', t('Hide block to selected roles'), [
                        'for' => 'visibility_roles_1'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('visibility_roles_2-group', 'div', function ($form) use ($data) {
                    $form->radio('visibility_roles', [
                        'checked'  => $data[ 'visibility_roles' ],
                        'id'       => 'visibility_roles_2',
                        'required' => 1,
                        'value'    => 1
                    ])->label('visibility_roles-label', t('Show block with selected roles'), [
                        'for' => 'visibility_roles_2'
                    ]);
                }, [ 'class' => 'form-group' ]);
                foreach (self::user()->getRoles() as $role) {
                    $form->group("role_{$role[ 'role_id' ]}-group", 'div', function ($form) use ($data, $role) {
                        $form->checkbox("roles[{$role[ 'role_id' ]}]", [
                            'checked' => in_array($role[ 'role_id' ], $data[ 'roles' ]),
                            'id'      => "role_{$role[ 'role_id' ]}",
                            'value'   => $role[ 'role_label' ]
                        ])
                        ->label(
                            'role_' . $role[ 'role_id' ] . '-label',
                            '<span class="ui"></span>'
                            . '<span class="badge-role" style="background-color: ' . $role[ 'role_color' ] . '">'
                            . '<i class="' . $role[ 'role_icon' ] . '" aria-hidden="true"></i>'
                            . '</span> '
                            . t($role[ 'role_label' ]),
                            [ 'for' => "role_{$role[ 'role_id' ]}" ]
                        );
                    }, [ 'class' => 'form-group' ]);
                }
            })
            ->token("token_block_$id")
            ->submit('submit_save', t('Save'), [ 'class' => 'btn btn-success' ])
            ->submit('submit_cancel', t('Cancel'), [ 'class' => 'btn btn-default' ]);

        $this->container->callHook('block.edit.form', [ &$form, $data ]);

        if (isset($_SESSION[ 'errors' ])) {
            unset($_SESSION[ 'errors_keys' ][ 'roles' ]);
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        return self::template()
                ->createBlock('block-form.php', $this->pathViews)
                ->addVars([
                    'form'      => $form,
                    'link_show' => self::router()->getRoute('block.show', [ ':id' => $data[ 'block_id' ] ])
        ]);
    }

    public function update($id, $req)
    {
        if (!self::query()->from('block')->where('block_id', '==', $id)->fetch()) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'title'            => '!required|string|max:255',
                'content'          => '!required|string|max:5000',
                'class'              => '!required|string|max:255',
                'visibility_pages' => 'bool',
                'pages'            => '!required|string|to_htmlsc',
                'visibility_roles' => 'bool',
                'roles'            => '!required|array',
                "token_block_$id"  => 'token'
            ])
            ->setLabel([
                'title'   => t('Title'),
                'content' => t('Content'),
                'pages'   => t('List of pages'),
                'roles'   => t('User Roles')
            ])
            ->setInputs($req->getParsedBody());

        $this->container->callHook('block.update.validator', [ &$validator ]);

        $validatorRoles = new Validator();
        if ($isValid = $validator->isValid()) {
            $listRoles = implode(',', self::query()->from('role')->lists('role_id'));
            foreach ($validator->getInput('roles', []) as $key => $role) {
                $validatorRoles
                    ->addRule($key, 'int|inarray:' . $listRoles)
                    ->addLabel($key, t($role))
                    ->addInput($key, $key);
            }
        }
        $isValid &= $validatorRoles->isValid();

        if ($isValid) {
            $idRoles = array_keys($validator->getInput('roles', []));
            $values = [
                'title'            => $validator->getInput('title'),
                'content'          => $validator->getInput('content'),
                'class'              => $validator->getInput('class'),
                'visibility_pages' => (bool) $validator->getInput('visibility_pages'),
                'pages'            => $validator->getInput('pages'),
                'visibility_roles' => (bool) $validator->getInput('visibility_roles'),
                'roles'            => implode(',', $idRoles)
            ];

            $this->container->callHook('block.update.before', [ $validator, &$values ]);
            self::query()
                ->update('block', $values)
                ->where('block_id', '==', $id)
                ->execute();
            $this->container->callHook('block.update.after', [ $validator ]);
        } else {
            $_SESSION[ 'inputs' ]      = $validator->getInputs();
            $_SESSION[ 'errors' ]      = $validator->getKeyErrors() + $validatorRoles->getKeyErrors();
            $_SESSION[ 'errors_keys' ] = $validator->getKeyInputErrors();

            return $this->edit($id, $req);
        }

        return $this->show($id, $req);
    }

    public function delete($id, $req)
    {
        if (!self::query()->from('block')->where('block_id', '==', $id)->fetch()) {
            return $this->get404($req);
        }

        $this->container->callHook('block.delete.before', [ $id ]);
        self::query()->from('block')->where('block_id', '==', $id)->delete()->execute();
        $this->container->callHook('block.delete.after', [ $id ]);
    }
}
