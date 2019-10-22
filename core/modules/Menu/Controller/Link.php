<?php

namespace SoosyzeCore\Menu\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

class Link extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function create($nameMenu)
    {
        $content = [ 'title_link' => '', 'icon' => '', 'link' => '', 'fragment' => '', 'target_link' => '_self' ];

        $this->container->callHook('menu.link.create.form.data', [ &$content ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.store', [ ':menu' => $nameMenu ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('menu-link-fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('menu-link-legend', t('Add a link in the menu'))
                ->group('menu-link-title-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-title-label', t('Link title'), [
                        'for' => 'title_link' ])
                    ->text('title_link', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => t('Example: Home'),
                        'required'    => 1,
                        'value'       => $content[ 'title_link' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-link-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-link-label', t('Link'))
                    ->text('link', [
                        'class'       => 'form-control',
                        'placeholder' => t('Example: node/1 or http://foo.com'),
                        'required'    => 1,
                        'value'       => $content[ 'link' ] . (!empty($content['fragment']) ? '#' . $content['fragment'] : '')
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-icon-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-icon-label', t('Icon'), [
                        'data-tooltip' => t('Icons are created from the CSS class of FontAwesome')
                    ])
                    ->group('menu-link-icon-group', 'div', function ($form) use ($content) {
                        $form->text('icon', [
                            'class'       => 'form-control text_icon',
                            'maxlength'   => 255,
                            'placeholder' => 'fa fa-bars, fa fa-home...',
                            'value'       => $content[ 'icon' ],
                        ])
                        ->html('btn-icon', '<button:css:attr>:_content</button>', [
                            '_content'     => '<i class="' . $content[ 'icon' ] . '" aria-hidden="true"></i>',
                            'aria-label'   => t('Rendering'),
                            'class'        => 'btn render_icon',
                            'type'         => 'button',
                            'data-tooltip' => t('Rendering')
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-target-group', 'div', function ($form) use ($content) {
                    $form->label('menu-link-target-label', t('Target'))
                    ->select('target_link', self::getTarget(), [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $content[ 'target_link' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_link_create')
            ->html('cancel', '<button:css:attr>:_content</button>', [
                '_content' => t('Cancel'),
                'class'    => 'btn btn-danger',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ])
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        $this->container->callHook('menu.link.create.form', [ &$form, $content ]);

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
                    'title_main' => '<i class="fa fa-bars" aria-hidden="true"></i> ' . t('Add a link')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu-link-add.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($nameMenu, $req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'title_link'        => 'required|string|max:255|striptags',
                'link'              => 'required',
                'icon'              => '!required|max:255|fontawesome:solid,brands',
                'target_link'       => 'required|inArray:_blank,_self,_parent,_top',
                'token_link_create' => 'required|token'
            ])
            ->setInputs($post);

        $isUrlOrRoute = self::menu()->isUrlOrRoute($post, $req->withMethod('GET'));

        $this->container->callHook('menu.link.store.validator', [ &$validator ]);

        if ($validator->isValid() && $isUrlOrRoute !== false) {
            $data = [
                'key'         => $isUrlOrRoute['key'],
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $isUrlOrRoute['link'],
                'fragment'    => $isUrlOrRoute['fragment'],
                'target_link' => $validator->getInput('target_link'),
                'menu'        => $nameMenu,
                'weight'      => 1,
                'parent'      => -1,
                'active'      => true
            ];

            $this->container->callHook('menu.link.store.before', [ &$validator, &$data ]);
            self::query()
                ->insertInto('menu_link', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.link.store.after', [ &$validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
            $route                 = self::router()->getRoute('menu.show', [ ':menu' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute['is_valid']) {
            $_SESSION[ 'messages' ][ 'errors' ][ 'link.route' ] = t('Link value is not a URL or a route');
            $_SESSION[ 'errors_keys' ][]                        = 'link';
        }

        $route = self::router()->getRoute('menu.link.create', [ ':menu' => $nameMenu ]);

        return new Redirect($route);
    }

    public function edit($name, $id, $req)
    {
        if (!($query = self::menu()->find($id))) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.link.edit.form.data', [ &$query ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $query = array_merge($query, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('menu.link.update', [
            ':menu' => $name,
            ':item' => $id
        ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('menu-link-fieldset', 'fieldset', function ($form) use ($query) {
                $form->legend('menu-link-legend', t('Edit a link in the menu'))
                ->group('menu-link-title-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-title-label', t('Link title'))
                    ->text('title_link', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => t('Example: Home'),
                        'required'    => 1,
                        'value'       => $query[ 'title_link' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-link-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-link-label', t('Link'))
                    ->text('link', [
                        'class'       => 'form-control',
                        'placeholder' => t('Example: node/1 or http://foo.com'),
                        'required'    => 1,
                        'value'       => $query[ 'link' ] . (!empty($query['fragment']) ? '#' . $query['fragment'] : '')
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-icon-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-icon-label', t('Icon'), [
                        'data-tooltip' => t('Icons are created from the CSS class of FontAwesome')
                    ])
                    ->group('menu-link-icon-group', 'div', function ($form) use ($query) {
                        $form->text('icon', [
                            'class'       => 'form-control text_icon',
                            'maxlength'   => 255,
                            'placeholder' => 'fa fa-bars, fa fa-home...',
                            'value'       => $query[ 'icon' ],
                        ])
                        ->html('btn-icon', '<button:css:attr>:_content</button>', [
                            '_content'     => '<i class="' . $query[ 'icon' ] . '" aria-hidden="true"></i>',
                            'aria-label'   => t('Rendering'),
                            'class'        => 'btn render_icon',
                            'type'         => 'button',
                            'data-tooltip' => t('Rendering')
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ])
                ->group('menu-link-target-group', 'div', function ($form) use ($query) {
                    $form->label('menu-link-target-label', t('Target'))
                    ->select('target_link', self::getTarget(), [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $query[ 'target_link' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_link_edit')
            ->html('cancel', '<button:css:attr>:_content</button>', [
                '_content' => t('Cancel'),
                'class'    => 'btn btn-danger',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ])
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        $this->container->callHook('menu.link.edit.form', [ &$form, $query ]);

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
                    'title_main' => '<i class="fa fa-bars" aria-hidden="true"></i> ' . t('Edit a link')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'menu-link-edit.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function update($nameMenu, $id, $req)
    {
        if (!self::menu()->find($id)) {
            return $this->get404($req);
        }

        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'title_link'      => 'required|string|max:255|htmlsc',
                'icon'            => '!required|max:255|fontawesome:solid,brands',
                'link'            => 'required',
                'target_link'     => 'required|inArray:_blank,_self,_parent,_top',
                'token_link_edit' => 'required|token'
            ])
            ->setLabel([
                'title_link'        => t('Link title'),
                'link'              => t('Link'),
                'icon'              => t('Icon'),
                'target_link'       => t('Target'),
            ])
            ->setInputs($post);

        $isUrlOrRoute = self::menu()->isUrlOrRoute($post, $req->withMethod('GET'));

        $this->container->callHook('menu.link.update.validator', [ &$validator ]);

        if ($validator->isValid() && $isUrlOrRoute !== false) {
            $data = [
                'key'         => $isUrlOrRoute['key'],
                'title_link'  => $validator->getInput('title_link'),
                'icon'        => $validator->getInput('icon'),
                'link'        => $isUrlOrRoute['link'],
                'fragment'    => $isUrlOrRoute['fragment'],
                'target_link' => $validator->getInput('target_link')
            ];

            $this->container->callHook('menu.link.update.before', [ &$validator, &$data ]);
            self::query()
                ->update('menu_link', $data)
                ->where('id', '==', $id)
                ->execute();
            $this->container->callHook('menu.link.update.after', [ &$validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
            $route                               = self::router()->getRoute('menu.show', [
                ':menu' => $nameMenu ]);

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        if (!$isUrlOrRoute['is_valid']) {
            $_SESSION[ 'messages' ][ 'errors' ][ 'link.route' ] = t('Link value is not a URL or a route');
            $_SESSION[ 'errors_keys' ][]                        = 'link';
        }

        $route = self::router()->getRoute('menu.link.edit', [
            ':menu' => $nameMenu,
            ':item' => $id
        ]);

        return new Redirect($route);
    }

    public function delete($name, $id, $req)
    {
        if (!self::menu()->find($id)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'name' => 'required|string|max:255|htmlsc',
                'id'   => 'required|int'
            ])
            ->setInputs([ 'name' => $name, 'id' => $id ]);

        $this->container->callHook('menu.link.delete.validator', [ &$validator, $id ]);

        if ($validator->isValid()) {
            $this->container->callHook('menu.link.delete.before', [ $validator, $id ]);
            self::query()
                ->from('menu_link')
                ->delete()
                ->where('id', '==', $id)
                ->execute();

            $this->container->callHook('menu.link.delete.after', [ $validator, $id ]);
        }

        $route = self::router()->getRoute('menu.show', [ ':menu' => $name ]);

        return new Redirect($route);
    }
    
    protected static function getTarget()
    {
        return [
            [ 'value' => '_blank', 'label' => '(_blank) ' . t('Load in a new window') ],
            [ 'value' => '_self', 'label' => '(_self) ' . t('Load in the same window') ],
            [ 'value' => '_parent', 'label' => '(_parent) ' . t('Load into the parent frameset') ],
            [ 'value' => '_top', 'label' => '(_top) ' . t('Load in the whole body of the window') ]
        ];
    }
}
