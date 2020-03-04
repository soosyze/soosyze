<?php

namespace SoosyzeCore\Node\Form;

use Soosyze\Components\Form\FormBuilder;

class FormNode extends FormBuilder
{
    protected $content = [
        'title'          => '',
        'meta_noindex'   => false,
        'meta_nofollow'  => false,
        'meta_noarchive' => false,
        'published'      => false
    ];

    protected static $field_rules = [
        'email',
        'month',
        'password',
        'search',
        'tel',
        'text',
        'textarea',
        'url',
        'week'
    ];

    protected $file;

    protected static $attrGrp = [ 'class' => 'form-group' ];

    protected $query;

    protected $fields;

    protected $router;

    public function __construct(
        array $attributes,
        $file,
        $query,
        $router
    ) {
        parent::__construct($attributes);
        $this->file   = $file;
        $this->query  = $query;
        $this->router = $router;
    }

    public function content($content, $type, $fields)
    {
        $this->content = array_merge($this->content, $content);
        $this->type    = $type;
        $this->fields  = $fields;

        return $this;
    }

    public function make()
    {
        return $this->title()
                ->fields()
                ->seo()
                ->actionsSubmit();
    }

    public function fields()
    {
        return $this->group('fields-fieldset', 'fieldset', function ($form) {
            $form->legend('fields-legend', t('Fill in the following fields'));
            foreach ($this->fields as $value) {
                $key                   = $value[ 'field_name' ];
                /* Si le contenu du champ n'existe pas alors il est déclaré vide. */
                $this->content[ $key ] = isset($this->content[ $key ])
                        ? $this->content[ $key ]
                        : '';
                $this->makeField($form, $value);
            }
        });
    }

    public function makeField(&$form, $value)
    {
        $key = $value[ 'field_name' ];
        $this->rules($value);

        return $form->group("$key-group", 'div', function ($form) use ($value, $key) {
            $options = !empty($value[ 'field_option' ])
                    ? json_decode($value[ 'field_option' ], true)
                    : [];
            switch ($value[ 'field_type' ]) {
                    case 'checkbox':
                        $this->makeCheckbox($form, $key, $value, $options);

                        break;
                    case 'file':
                    case 'image':
                        $form->label("$key-label", t($value[ 'field_label' ]));
                        $this->file->inputFile($key, $form, $this->content[ $key ], $value[ 'field_type' ]);

                        break;
                    case 'one_to_many':
                        $this->makeOneToMany($form, $key, $value, $options);

                        break;
                    case 'radio':
                        $this->makeRadio($form, $key, $value, $options);

                        break;
                    case 'select':
                        $this->makeSelect($form, $key, $value, $options);

                        break;
                    case 'textarea':
                        $this->makeTextarea($form, $key, $value, $options);

                        break;
                    default:
                        $this->makeInput($form, $key, $value, $options);

                        break;
                }
        }, self::$attrGrp);
    }

    //field_default_value
    public function makeInput(&$form, $key, $value, $options)
    {
        $type    = $value[ 'field_type' ];
        $default = empty($this->content[ $key ])
            ? $value[ 'field_default_value' ]
            : $this->content[ $key ];
        $form->label("$key-label", t($value[ 'field_label' ]))
            ->$type($key, [
                'class'       => 'form-control',
                'placeholder' => t($value[ 'field_label' ]),
                'value'       => $default
                ] + $value[ 'attr' ]);
    }

    public function makeCheckbox(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]));
        foreach ($options as $key_radio => $option) {
            $form->group("$key_radio-group", 'div', function ($form) use ($key, $key_radio, $value, $option) {
                $form->checkbox($key . "[$key_radio]", [
                        'id'      => "$key-$key_radio",
                        'checked' => in_array($key_radio, explode(',', $this->content[ $key ])),
                        'value'   => $key_radio
                    ])
                    ->label("$key-$key_radio-label", '<span class="ui"></span> ' . t($option), [
                        'for' => "$key-$key_radio"
                        ] + $value[ 'attr' ]);
            }, self::$attrGrp);
        }
    }

    public function makeOneToMany($form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
            'required' => !empty($value['attr']['required'])
        ]);
        if (!isset($this->content[ 'entity_id' ])) {
            $form->html('add-' . $key, '<div:attr><p>:_content</p></div>', [
                '_content' => t('Enregistrer votre contenu avant de pouvoir ajouter des éléments.'),
                'class'    => 'block-content-disabled',
                'style'    => 'cursor:not-allowed'
            ]);

            return;
        }
        
        $data = $this->query
                ->from($options['relation_table'])
                ->where($options['foreign_key'], $this->content[ 'entity_id' ]);
        if (isset($options['order_by'])) {
            $data->orderBy($options['order_by'], $options['sort']);
        }
        $sub_fields = $data->fetchAll();
        $form->group("$key-group", 'div', function ($form) use ($key, $sub_fields, $options) {
            foreach ($sub_fields as $field) {
                $id_entity = $field[ "{$key}_id" ];
                $form->group("$key-$id_entity-group", 'div', function ($form) use ($key, $id_entity, $options, $field) {
                    if (isset($options[ 'order_by' ]) && $options[ 'sort' ] == 'weight') {
                        $form->html("$key-$id_entity-drag", '<i class="fa fa-arrows-alt" aria-hidden="true"></i>')
                            ->hidden("{$key}[$id_entity][weight]", [
                                'value' => $field[ 'weight' ]
                            ])->hidden("{$key}[$id_entity][id]", [
                            'value' => $id_entity
                        ]);
                    }
                    $form->html("$key-$id_entity-delete", '<a:attr>:_content</a>', [
                            '_content' => '<i class="fa fa-times"></i>',
                            'class'    => 'btn',
                            'href'     => $this->router->getRoute('entity.delete', [
                                ':id_node'   => $this->content[ 'id' ],
                                ':entity'    => $key,
                                ':id_entity' => $field[ "{$key}_id" ]
                            ]),
                        ])
                        ->html("$key-$id_entity-edit", '<a:attr>:_content</a>', [
                            'href'     => $this->router->getRoute('entity.edit', [
                                ':id_node'   => $this->content[ 'id' ],
                                ':entity'    => $key,
                                ':id_entity' => $field[ "{$key}_id" ]
                            ]),
                            '_content' => $field[ $options[ 'field_show' ] ]
                    ]);
                }, ['class' => 'sort_weight']);
            }
        }, [ 'class' => $options['sort'] === 'weight' ? 'nested-sortable form-group' : 'form-group' ]);

        if (!isset($value[ 'attr' ][ 'max' ]) || $value[ 'attr' ][ 'max' ] > count($sub_fields)) {
            $form->group("add-$key-group", 'div', function ($form) use ($key) {
                $form->html('add-' . $key, '<a:attr>:_content</a>', [
                    'href'     => $this->router->getRoute('entity.create', [
                        ':id_node' => $this->content[ 'id' ],
                        ':entity'  => $key,
                    ]),
                    '_content' => '<i class="fa fa-plus"></i> ' . t('Ajouter un contenu')
                ]);
            });
        }
    }

    public function makeRadio(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]));
        foreach ($options as $key_radio => $option) {
            $form->group("$key_radio-group", 'div', function ($form) use ($key, $value, $key_radio, $option) {
                $form->radio($key, [
                        'id'      => "$key-$key_radio",
                        'checked' => $this->content[ $key ] == $key_radio,
                        'value'   => $key_radio
                        ] + $value[ 'attr' ])
                    ->label("$key-$key_radio-label", '<span class="ui"></span> ' . $option, [
                        'for' => "$key-$key_radio"
                ]);
            }, self::$attrGrp);
        }
    }

    public function makeSelect(&$form, $key, $value, $options)
    {
        $select_options = [];
        foreach ($options as $key_option => $option) {
            $select_options[ $key_option ] = [ 'label' => $option, 'value' => $key_option ];
            if ($key_option == $this->content[ $key ]) {
                $select_options[ $key_option ][ 'selected' ] = 1;
            }
        }

        $form->label("$key-label", t($value[ 'field_label' ]))
            ->select($key, $select_options, [ 'class' => 'form-control' ] + $value[ 'attr' ]);
    }
    
    public function makeTextarea(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]))
            ->textarea($key, $this->content[ $key ], [
                'class'       => 'form-control editor',
                'rows'        => 8,
                'placeholder' => t($value[ 'field_label' ])
                ] + $value[ 'attr' ]);
    }

    public function title()
    {
        return $this->group('title-group', 'div', function ($form) {
            $form->label('title-label', t('Title of the content'))
                    ->text('title', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'required'    => 1,
                        'placeholder' => t('Title of the content'),
                        'value'       => $this->content[ 'title' ]
                ]);
        }, self::$attrGrp);
    }

    public function seo()
    {
        return $this->group('seo-group', 'fieldset', function ($form) {
            $form->legend('seo-legend', t('SEO'))
                    ->group('meta_noindex-group', 'div', function ($form) {
                        $form->checkbox('meta_noindex', [ 'checked' => $this->content[ 'meta_noindex' ] ])
                        ->label('meta_noindex-label', '<span class="ui"></span> ' . t('Bloquer l\'indexation') . ' <code>noindex</code>', [
                            'for' => 'meta_noindex'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('meta_nofollow-group', 'div', function ($form) {
                        $form->checkbox('meta_nofollow', [ 'checked' => $this->content[ 'meta_nofollow' ] ])
                        ->label('meta_nofollow-label', '<span class="ui"></span> ' . t('Bloquer le suivi des liens') . ' <code>nofollow</code>', [
                            'for' => 'meta_nofollow'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_noarchive-group', 'div', function ($form) {
                        $form->checkbox('meta_noarchive', [ 'checked' => $this->content[ 'meta_noarchive' ] ])
                        ->label('meta_noarchive-label', '<span class="ui"></span> ' . t('Bloquer la mise en cache') . ' <code>noarchive</code>', [
                            'for' => 'meta_nofollow'
                        ]);
                    }, self::$attrGrp);
        });
    }

    public function actionsSubmit()
    {
        return $this->group('node-publish-group', 'div', function ($form) {
            $form->checkbox('published', [ 'checked' => $this->content[ 'published' ] ])
                    ->label('publish-label', '<span class="ui"></span> ' . t('Publish content'), [
                        'for' => 'published'
                    ]);
        }, self::$attrGrp)
                ->token('token_node')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
    }

    public function actionsEntitySubmit()
    {
        return $this->token('token_entity')
                ->html('cancel', '<button:attr>:_content</button>', [
                    '_content' => t('Cancel'),
                    'class'    => 'btn btn-danger',
                    'onclick'  => 'javascript:history.back();',
                    'type'     => 'button'
                ])
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
    }

    public function rules(&$value)
    {
        $value[ 'attr' ] = [];
        if (preg_match('/^(.*\|)?required(\|.*)?/', $value[ 'field_rules' ])) {
            $value[ 'attr' ][ 'required' ] = 1;
        }
        if (preg_match('/[\|]?(max|max_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $value[ 'field_rules' ], $matches)) {
            if (in_array($value[ 'field_type' ], self::$field_rules)) {
                $value[ 'attr' ][ 'maxlength' ] = (int) $matches[ 2 ];
            } elseif (in_array($value[ 'field_type' ], [ 'number', 'date', 'one_to_many' ])) {
                $value[ 'attr' ][ 'max' ] = (int) $matches[ 2 ];
            }
        }
        if (preg_match('/[\|]?(min|min_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $value[ 'field_rules' ], $matches)) {
            if (in_array($value[ 'field_type' ], self::$field_rules)) {
                $value[ 'attr' ][ 'minlength' ] = (int) $matches[ 2 ];
            } elseif (in_array($value[ 'field_type' ], [ 'number', 'date', 'one_to_many' ])) {
                $value[ 'attr' ][ 'min' ] = (int) $matches[ 2 ];
            }
        }
    }
}
