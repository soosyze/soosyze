<?php

namespace SoosyzeCore\Node\Services;

class HookMenu
{
    private $alias;

    /**
     * Si le module menu existe.
     *
     * @var bool
     */
    private $isMenu;

    /**
     * @var \Queryflatfile\Request
     */
    private $query;

    /**
     * @var \Queryflatfile\Schema
     */
    private $schema;

    public function __construct($alias, $query, $schema)
    {
        $this->alias  = $alias;
        $this->query  = $query;
        $this->schema = $schema;

        $this->isMenu = $this->schema->hasTable('menu');
    }

    public function hookCreateFormData(&$data)
    {
        if ($this->isMenu) {
            $data[ 'title_link' ] = '';
            $data[ 'active' ]     = '';
        }
    }

    public function hookEditFormData(&$data, $idNode)
    {
        if ($this->isMenu) {
            $data[ 'title_link' ] = '';
            $data[ 'active' ]     = '';

            $link = $this->query
                ->from('node_menu_link')
                ->leftJoin('menu_link', 'menu_link_id', '=', 'menu_link.id')
                ->where('node_id', '==', $idNode)
                ->fetch();

            if ($link) {
                $data[ 'title_link' ] = $link[ 'title_link' ];
                $data[ 'active' ]     = (bool) $link[ 'menu_link_id' ];
            }
        }
    }

    public function hookCreateForm($form, $data)
    {
        if ($this->isMenu) {
            $form->before('actions-group', function ($form) use ($data) {
                $form->group('menu-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('menu-legend', t('Menu'))
                        ->group('active-group', 'div', function ($form) use ($data) {
                            $form->checkbox('active', [
                                'checked' => $data[ 'active' ],
                                'onclick' => 'toggle("menu_toogle")'
                            ])
                            ->label('active-label', '<span class="ui"></span> ' . t('Add a link in the menu'), [
                                'for' => 'active'
                            ]);
                        }, [ 'class' => 'form-group' ])
                        ->group('title_link-group', 'div', function ($form) use ($data) {
                            $form->group('title_link-group', 'div', function ($form) use ($data) {
                                $form->label('title_link-label', t('Link title'))
                                ->text('title_link', [
                                    'class'       => 'form-control',
                                    'placeholder' => t('Example: Home'),
                                    'value'       => $data[ 'title_link' ]
                                ]);
                            }, [ 'class' => 'form-group' ]);
                        }, [
                            'id'    => 'menu_toogle',
                            'style' => !$data[ 'active' ]
                                ? 'display:none'
                                : ''
                    ]);
                }, [
                    'class' => 'tab-pane fade',
                    'id'    => 'menu-fieldset'
                ]);
            });
        }
    }

    public function hookStoreValidator($validator)
    {
        if ($this->isMenu && $validator->hasInput('active')) {
            $validator->addRule('title_link', 'required|string|max:255|to_striptags')
                ->addRule('active', 'bool');
        }
    }

    public function hookStoreValid($validator)
    {
        if ($this->isMenu) {
            if (!$validator->hasInput('active')) {
                return;
            }

            $id    = $this->schema->getIncrement('node');
            $link  = 'node/' . $id;
            if ($alias = $this->alias->getAlias('node/' . $id)) {
                $link = $alias;
            }

            $this->query->insertInto('menu_link', [
                    'key', 'title_link', 'link', 'menu', 'weight', 'parent', 'active'
                ])
                ->values([
                    'node.show',
                    $validator->getInput('title_link'),
                    $link,
                    'menu-main',
                    1,
                    -1,
                    $validator->getInput('node_status_id') == 1,
                ])
                ->execute();

            $linkId = $this->schema->getIncrement('menu_link');

            $this->query->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
                ->values([ $id, $linkId ])
                ->execute();
        }
    }

    public function hookUpdateValid($validator, $id)
    {
        if ($this->isMenu) {
            $nodeMenuLink = $this->query->from('node_menu_link')
                ->where('node_id', '==', $id)
                ->fetch();

            $link  = 'node/' . $id;
            if ($alias = $this->alias->getAlias('node/' . $id)) {
                $link = $alias;
            }

            if ($validator->hasInput('active') && $nodeMenuLink) {
                $this->query->update('menu_link', [
                        'title_link' => $validator->getInput('title_link'),
                        'link'       => $link,
                        'active'     => $validator->getInput('node_status_id') == 1,
                    ])
                    ->where('id', $nodeMenuLink[ 'menu_link_id' ])
                    ->execute();
            } elseif ($validator->hasInput('active') && !$nodeMenuLink) {
                $this->query->insertInto('menu_link', [ 'key', 'title_link', 'link',
                        'menu', 'weight', 'parent', 'active' ])
                    ->values([
                        'node.show',
                        $validator->getInput('title_link'),
                        $link,
                        'menu-main',
                        1,
                        -1,
                        $validator->getInput('node_status_id') == 1,
                    ])
                    ->execute();

                $linkId = $this->schema->getIncrement('menu_link');

                $this->query->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
                    ->values([ $id, $linkId ])
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
        if (!($response instanceof \SoosyzeCore\Template\Services\Templating)) {
            return;
        }

        $script = $response->getBlock('this')->getVar('scripts');
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
        $response->view('this', [ 'scripts' => $script ]);
    }

    public function hookDeleteValid($validator, $item)
    {
        if ($this->isMenu) {
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
