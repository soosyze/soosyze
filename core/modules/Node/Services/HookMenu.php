<?php

namespace SoosyzeCore\Node\Services;

class HookMenu
{
    /**
     * @var \Queryflatfile\Schema
     */
    private $schema;

    /**
     * @var \Queryflatfile\Request
     */
    private $query;

    /**
     * Si le module menu existe.
     *
     * @var bool
     */
    private $is_menu;

    public function __construct($schema, $query)
    {
        $this->schema  = $schema;
        $this->query   = $query;
        $this->is_menu = $this->schema->hasTable('menu');
    }

    public function hookCreateFormData(&$data)
    {
        if ($this->is_menu) {
            $data[ 'title_link' ] = '';
            $data[ 'active' ]     = '';
        }
    }

    public function hookEditFormData(&$data, $item)
    {
        if ($this->is_menu) {
            $data[ 'title_link' ] = '';
            $data[ 'active' ]     = '';
            $link                 = $this->query
                ->from('node_menu_link')
                ->leftJoin('menu_link', 'menu_link_id', '=', 'menu_link.id')
                ->where('node_id', '==', $item)
                ->fetch();

            if ($link) {
                $data[ 'title_link' ] = $link[ 'title_link' ];
                $data[ 'active' ]     = (bool) $link[ 'menu_link_id' ];
            }
        }
    }

    public function hookCreateForm($form, $data)
    {
        if ($this->is_menu) {
            $form->addBefore('node-publish-group', function ($form) use ($data) {
                $form->group('node-menu-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('node-menu-legend', 'Menu')
                        ->group('node-menu-active-group', 'div', function ($form) use ($data) {
                            $form->checkbox('active', 'menu', [
                                'checked' => $data[ 'active' ],
                                'onclick' => 'toggle(\'menu_toogle\')'
                            ])
                            ->label('node-menu-active-label', '<span class="ui"></span> Ajouter un lien dans le menu', [
                                'for' => 'menu'
                            ]);
                        }, [ 'class' => 'form-group' ])
                        ->group('node-menu', 'div', function ($form) use ($data) {
                            $form->group('node-menu-title-group', 'div', function ($form) use ($data) {
                                $form->label('node-menu-title-label', 'Titre du lien', [
                                    'for' => 'title_link' ])
                                ->text('title_link', 'title_link', [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Exemple: Ma page 1',
                                    'value'       => $data[ 'title_link' ]
                                ]);
                            }, [ 'class' => 'form-group' ]);
                        }, [
                            'id'    => 'menu_toogle',
                            'style' => !$data[ 'active' ]
                                ? 'display:none'
                                : ''
                    ]);
                });
            });
        }
    }

    public function hookStoreValidator($validator)
    {
        if ($this->is_menu && $validator->hasInput('active')) {
            $validator->addRule('title_link', 'required|string|max:255|striptags')
                ->addRule('active', 'bool');
        }
    }

    public function hookStoreValid($validator)
    {
        if ($this->is_menu) {
            if (!$validator->hasInput('active')) {
                return;
            }

            $nodeLast = $this->query->select('id')
                ->from('node')
                ->where('title', $validator->getInput('title'))
                ->orderBy('created', 'desc')
                ->fetch();

            if ($nodeLast) {
                $id = $nodeLast[ 'id' ];
                $this->query->insertInto('menu_link', [
                    'key', 'title_link', 'link', 'menu', 'weight', 'parent', 'active'
                    ])
                    ->values([
                        'node.show',
                        $validator->getInput('title_link'),
                        'node/' . $id,
                        'menu-main',
                        1,
                        -1,
                        (bool) $validator->getInput('published'),
                    ])
                    ->execute();

                $linkLast = $this->query->from('menu_link')
                    ->where('title_link', $validator->getInput('title_link'))
                    ->where('link', 'node/' . $id)
                    ->fetch();

                $this->query->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
                    ->values([ $id, $linkLast[ 'id' ] ])
                    ->execute();
            }
        }
    }

    public function hookUpdateValid($validator, $id)
    {
        if ($this->is_menu) {
            $nodeMenuLink = $this->query->from('node_menu_link')
                ->where('node_id', '==', $id)
                ->fetch();

            if ($validator->hasInput('active') && $nodeMenuLink) {
                $this->query->update('menu_link', [
                        'title_link' => $validator->getInput('title_link'),
                        'active'     => (bool) $validator->getInput('published'),
                    ])
                    ->where('id', $nodeMenuLink[ 'menu_link_id' ])
                    ->execute();
            } elseif ($validator->hasInput('active') && !$nodeMenuLink) {
                $this->query->insertInto('menu_link', [ 'key', 'title_link', 'link',
                        'menu', 'weight', 'parent', 'active' ])
                    ->values([
                        'node.show',
                        $validator->getInput('title_link'),
                        'node/' . $id,
                        'menu-main',
                        1,
                        -1,
                        (bool) $validator->getInput('published'),
                    ])
                    ->execute();

                $linkLast = $this->query->from('menu_link')
                    ->where('title_link', $validator->getInput('title_link'))
                    ->where('link', 'node/' . $id)
                    ->fetch();

                $this->query->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
                    ->values([ $id, $linkLast[ 'id' ] ])
                    ->execute();
            } elseif (!$validator->hasInput('active') && $nodeMenuLink) {
                $this->query->from('node_menu_link')
                    ->where('node_id', '==', $id)
                    ->delete()
                    ->execute();

                $this->query->from('menu_link')
                    ->where('id', '==', $nodeMenuLink[ 'menu_link_id' ])
                    ->delete()
                    ->execute();
            }
        }
    }

    public function getForm($request, &$response)
    {
        $script = $response->getVar('scripts');
        $script .= '<script>
                function toggle (id) {
                    var item             = document.getElementById(id);
                    var input_title      = document.getElementById("title");
                    var input_title_link = document.getElementById("title_link");
                    
                    item.style.display     = item.style.display == "none" ? "" : "none";
                    input_title_link.value = input_title_link.value 
                        ? input_title_link.value 
                        : input_title.value;
                }
            </script>';
        $response->add([ 'scripts' => $script ]);
    }

    public function hookDeleteValid($validator, $item)
    {
        if ($this->is_menu) {
            $nodeMenuLink = $this->query->from('node_menu_link')
                ->where('node_id', '==', $item)
                ->fetch();

            if ($nodeMenuLink) {
                $this->query->from('node_menu_link')
                    ->where('node_id', '==', $item)
                    ->delete()
                    ->execute();

                $this->query->from('menu_link')
                    ->where('id', '==', $nodeMenuLink[ 'menu_link_id' ])
                    ->delete()
                    ->execute();
            }
        }
    }

    public function hookLinkDeleteValid($validator, $id)
    {
        $this->query->from('node_menu_link')
            ->where('menu_link_id', '==', $id)
            ->delete()
            ->execute();
    }
}
