<?php

namespace SoosyzeCore\Node\Form;

use Soosyze\Components\Form\FormBuilder;

class FormNode extends FormBuilder
{
    protected $content = [
        'title'            => '',
        'meta_description' => '',
        'meta_noindex'     => false,
        'meta_nofollow'    => false,
        'meta_noarchive'   => false,
        'meta_title'       => '',
        'sticky'           => false,
        'node_status_id'   => 3,
        'date_created'     => '',
        'id'               => null
    ];

    protected static $fieldRules = [
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

    protected static $attrGrpInline = [ 'class' => 'form-group-inline' ];

    protected $query;

    protected $fields;

    protected $router;

    protected $config;

    public function __construct(
        array $attributes,
        $file,
        $query,
        $router,
        $config
    ) {
        parent::__construct($attributes);
        $this->file   = $file;
        $this->query  = $query;
        $this->router = $router;
        $this->config = $config;
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
        return $this
                ->fields()
                ->seo()
                ->actionsSubmit();
    }

    public function fields()
    {
        return $this->group('fields-fieldset', 'fieldset', function ($form) {
            $form->group('title-group', 'div', function ($form) {
                $form->label('title-label', t('Title of the content'))
                        ->text('title', [
                            'class'     => 'form-control',
                            'maxlength' => 255,
                            'required'  => 1,
                            'value'     => $this->content[ 'title' ]
                    ]);
            }, self::$attrGrp);
            foreach ($this->fields as $value) {
                $key                   = $value[ 'field_name' ];
                /* Si le contenu du champ n'existe pas alors il est déclaré vide. */
                $this->content[ $key ] = isset($this->content[ $key ])
                        ? $this->content[ $key ]
                        : '';
                $this->makeField($form, $value);
            }
        }, [
                'class' => 'tab-pane active fade',
                'id'    => 'fields-fieldset'
        ]);
    }

    public function fieldsEntity()
    {
        return $this->group('fields-fieldset', 'fieldset', function ($form) {
            foreach ($this->fields as $value) {
                $key                   = $value[ 'field_name' ];
                /* Si le contenu du champ n'existe pas alors il est déclaré vide. */
                $this->content[ $key ] = isset($this->content[ $key ])
                        ? $this->content[ $key ]
                        : '';
                $this->makeField($form, $value);
            }
        }, [
                'class' => 'tab-pane active fade',
                'id'    => 'fields-fieldset'
        ]);
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
                        $form->label("$key-label", t($value[ 'field_label' ]), [
                            'data-tooltip' => t($value[ 'field_description' ])
                        ]);
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
                    case 'number':
                        $this->makeNumber($form, $key, $value, $options);

                        break;
                    default:
                        $this->makeInput($form, $key, $value, $options);

                        break;
                }
        }, self::$attrGrp);
    }

    public function makeNumber(&$form, $key, $value, $options)
    {
        $default = empty($this->content[ $key ])
            ? $value[ 'field_default_value' ]
            : $this->content[ $key ];

        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ]),
                'for'          => $key,
                'required'     => !empty($value[ 'attr' ][ 'required' ])
            ])
            ->group("$key-flex", 'div', function ($form) use ($key, $value, $default) {
                $form->number($key, [
                    ':actions' => 1,
                    'class'    => 'form-control',
                    'value'    => $default
                    ] + $value[ 'attr' ]);
            }, [ 'class' => 'form-group-flex' ]);
    }

    public function makeInput(&$form, $key, $value, $options)
    {
        $type    = $value[ 'field_type' ];
        $default = empty($this->content[ $key ])
            ? $value[ 'field_default_value' ]
            : $this->content[ $key ];

        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ])
            ])
            ->$type($key, [
                'class' => 'form-control',
                'value' => $default
                ] + $value[ 'attr' ]);
    }

    public function makeCheckbox(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
            'data-tooltip' => t($value[ 'field_description' ])
        ]);
        foreach ($options as $keyRadio => $option) {
            $form->group("$keyRadio-group", 'div', function ($form) use ($key, $keyRadio, $value, $option) {
                $form->checkbox("{$key}[$keyRadio]", [
                        'id'      => "$key-$keyRadio",
                        'checked' => in_array($keyRadio, explode(',', $this->content[ $key ])),
                        'value'   => $keyRadio
                    ])
                    ->label("$key-$keyRadio-label", '<span class="ui"></span> ' . t($option), [
                        'for' => "$key-$keyRadio"
                        ] + $value[ 'attr' ]);
            }, self::$attrGrp);
        }
    }

    public function makeOneToMany($form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
            'required'     => !empty($value[ 'attr' ][ 'required' ]),
            'data-tooltip' => t($value[ 'field_description' ])
        ]);
        if (!isset($this->content[ 'entity_id' ])) {
            $form->html('add-' . $key, '<div:attr><p>:_content</p></div>', [
                '_content' => t('Save your content before you can add items'),
                'class'    => 'block-content-disabled',
                'style'    => 'cursor:not-allowed'
            ]);

            return;
        }

        $data = $this->query
            ->from($options[ 'relation_table' ])
            ->where($options[ 'foreign_key' ], $this->content[ 'entity_id' ]);
        if (isset($options[ 'order_by' ])) {
            $data->orderBy($options[ 'order_by' ], $options[ 'sort' ]);
        }

        $subFields = $data->fetchAll();
        $dir = $this->router->getBasePath();
        $form->group("$key-group", 'div', function ($form) use ($key, $subFields, $options, $dir) {
            foreach ($subFields as $field) {
                $idEntity = $field[ "{$key}_id" ];
                $form->group("$key-$idEntity-group", 'div', function ($form) use ($key, $idEntity, $options, $field, $dir) {
                    if (isset($options[ 'order_by' ]) && $options[ 'sort' ] == 'weight') {
                        $form->html("$key-$idEntity-drag", '<i class="fa fa-arrows-alt-v" aria-hidden="true"></i>')
                            ->hidden("{$key}[$idEntity][weight]", [
                                'value' => $field[ 'weight' ]
                            ])->hidden("{$key}[$idEntity][id]", [
                            'value' => $idEntity
                        ]);
                    }

                    $content = $field[ $options[ 'field_show' ] ];
                    if ($this->isShowFile($options, $field)) {
                        $src     = $dir . $field[ $options[ 'field_type_show' ] ];
                        $content = "<img src='$src' class='img-thumbnail img-thumbnail-light'/>";
                    }

                    $form->html("$key-$idEntity-show", '<a:attr>:_content</a>', [
                            '_content' => $content,
                            'href'     => $this->router->getRoute('entity.edit', [
                                ':id_node'   => $this->content[ 'id' ],
                                ':entity'    => $key,
                                ':id_entity' => $field[ "{$key}_id" ]
                            ]),
                        ])
                        ->html("$key-$idEntity-edit", '<a:attr>:_content</a>', [
                            '_content' => '<i class="fa fa-edit" aria-hidden="true"></i> ' . t('Edit'),
                            'class'    => 'btn',
                            'href'     => $this->router->getRoute('entity.edit', [
                                ':id_node'   => $this->content[ 'id' ],
                                ':entity'    => $key,
                                ':id_entity' => $field[ "{$key}_id" ]
                            ]),
                        ])
                        ->html("$key-$idEntity-delete", '<a:attr>:_content</a>', [
                            '_content' => '<i class="fa fa-times" aria-hidden="true"></i> ' . t('Delete'),
                            'class'    => 'btn',
                            'href'     => $this->router->getRoute('entity.delete', [
                                ':id_node'   => $this->content[ 'id' ],
                                ':entity'    => $key,
                                ':id_entity' => $field[ "{$key}_id" ]
                            ]),
                    ]);
                }, [ 'class' => 'sort_weight draggable draggable-verticale node-draggable_one_to_many' ]);
            }
        }, [ 'class' => $options[ 'sort' ] === 'weight'
                ? 'nested-sortable form-group'
                : 'form-group' ]);

        if (!isset($value[ 'attr' ][ 'max' ]) || $value[ 'attr' ][ 'max' ] > count($subFields)) {
            $form->group("add-$key-group", 'div', function ($form) use ($key) {
                $form->html('add-' . $key, '<a:attr>:_content</a>', [
                    'href'     => $this->router->getRoute('entity.create', [
                        ':id_node' => $this->content[ 'id' ],
                        ':entity'  => $key,
                    ]),
                    '_content' => '<i class="fa fa-plus" aria-hidden="true"></i> ' . t('Add content')
                ]);
            });
        }
    }
    
    public function isShowFile($options, $field)
    {
        return isset($options[ 'field_type_show' ]) && $options[ 'field_type_show' ] === 'image' && is_file($field[ $options[ 'field_type_show' ] ]);
    }

    public function makeRadio(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
            'data-tooltip' => t($value[ 'field_description' ])
        ]);
        foreach ($options as $keyRadio => $option) {
            $form->group("$keyRadio-group", 'div', function ($form) use ($key, $value, $keyRadio, $option) {
                $form->radio($key, [
                        'id'      => "$key-$keyRadio",
                        'checked' => $this->content[ $key ] == $keyRadio,
                        'value'   => $keyRadio
                        ] + $value[ 'attr' ])
                    ->label("$key-$keyRadio-label", '<span class="ui"></span> ' . t($option), [
                        'for' => "$key-$keyRadio"
                ]);
            }, self::$attrGrp);
        }
    }

    public function makeSelect(&$form, $key, $value, $options)
    {
        $selectOptions = [];
        foreach ($options as $keyOption => $option) {
            $selectOptions[ $keyOption ] = [ 'label' => $option, 'value' => $keyOption ];
            if ($keyOption == $this->content[ $key ]) {
                $selectOptions[ $keyOption ][ 'selected' ] = 1;
            }
        }

        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ])
            ])
            ->select($key, $selectOptions, [ 'class' => 'form-control' ] + $value[ 'attr' ]);
    }

    public function makeTextarea(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ])
            ])
            ->textarea($key, $this->content[ $key ], [
                'class' => 'form-control editor',
                'rows'  => 8
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
        return $this->group('seo-fieldset', 'fieldset', function ($form) {
            $form->legend('seo-legend', t('SEO'))
                    ->group('meta_title-group', 'div', function ($form) {
                        $form->label('meta_title-label', t('Title'), [
                            'data-tooltip' => t('Leave blank to use the site\'s default title')
                        ])
                        ->text('meta_title', [
                            'class'       => 'form-control',
                            'placeholder' => ':page_title | :site_title',
                            'value'       => $this->content[ 'meta_title' ]
                        ])
                        ->html('meta_title-info', '<p>:_content</p>', [
                            '_content' => t('Variables allowed') . ' <code>:page_title</code>, <code>:site_title</code>, <code>:site_description</code>'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_description-group', 'div', function ($form) {
                        $form->label('meta_description-label', t('Description'), [
                            'data-tooltip' => t('Leave blank to use the default site description')
                        ])
                        ->textarea('meta_description', $this->content[ 'meta_description' ], [
                            'class' => 'form-control',
                            'rows'  => 3
                        ])
                        ->html('meta_description-info', '<p>:_content</p>', [
                            '_content' => t('Variables allowed') . ' <code>:page_title</code>, <code>:site_title</code>, <code>:site_description</code>'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_noindex-group', 'div', function ($form) {
                        $form->checkbox('meta_noindex', [ 'checked' => $this->content[ 'meta_noindex' ] ])
                        ->label('meta_noindex-label', '<span class="ui"></span> ' . t('Block indexing') . ' <code>noindex</code>', [
                            'for' => 'meta_noindex'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_nofollow-group', 'div', function ($form) {
                        $form->checkbox('meta_nofollow', [ 'checked' => $this->content[ 'meta_nofollow' ] ])
                        ->label('meta_nofollow-label', '<span class="ui"></span> ' . t('Block link tracking') . ' <code>nofollow</code>', [
                            'for' => 'meta_nofollow'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_noarchive-group', 'div', function ($form) {
                        $form->checkbox('meta_noarchive', [ 'checked' => $this->content[ 'meta_noarchive' ] ])
                        ->label('meta_noarchive-label', '<span class="ui"></span> ' . t('Block caching') . ' <code>noarchive</code>', [
                            'for' => 'meta_noarchive'
                        ]);
                    }, self::$attrGrp);
        }, [
                'class' => 'tab-pane fade',
                'id'    => 'seo-fieldset'
        ]);
    }

    public function actionsSubmit()
    {
        return $this
                ->group('publication-fieldset', 'fieldset', function ($form) {
                    $form
                    ->legend('publication-legend', t('Publication'))
                    ->group('sticky-group', 'div', function ($form) {
                        $form->checkbox('sticky', [ 'checked' => $this->content[ 'sticky' ] ])
                        ->label('sticky-label', '<span class="ui"></span> <i class="fa fa-thumbtack" aria-hidden="true"></i> ' . t('Pin content'), [
                            'for' => 'sticky'
                        ]);
                    }, self::$attrGrp)
                    ->group('date_created-group', 'div', function ($form) {
                        $form->label('date_created-label', t('Publication date'), [
                            'data-tooltip' => t('Leave blank to use the form submission date. It must be less than or equal to today\'s date')
                        ])
                        ->text('date_created', [
                            'class'       => 'form-control',
                            'maxlength'   => 19,
                            'placeholder' => t('YYYY-MM-DD Hours:Minutes:Seconds'),
                            'value'       => $this->getDateCreated()
                        ]);
                    }, self::$attrGrp)
                    ->label('date_created-label', t('Publication status'))
                    ->group('node_status-group', 'div', function ($form) {
                        $this->query->from('node_status');
                        if (!$this->config->get('settings.node_cron')) {
                            $this->query->where('node_status_id', '!=', 2);
                        }
                        $status = $this->query->fetchAll();
                        foreach ($status as $value) {
                            $form->group("node_status_id-{$value[ 'node_status_id' ]}-group", 'div', function ($form) use ($value) {
                                $form->radio('node_status_id', [
                                    'id'      => "node_status_id-{$value[ 'node_status_id' ]}",
                                    'checked' => $this->content[ 'node_status_id' ] == $value[ 'node_status_id' ],
                                    'class'   => 'radio-button',
                                    'value'   => $value[ 'node_status_id' ]
                                ])
                                ->label('node_status_id-label', t($value[ 'node_status_name' ]), [
                                    'class ' => 'radio-button',
                                    'for'    => "node_status_id-{$value[ 'node_status_id' ]}"
                                ]);
                            }, self::$attrGrpInline);
                        }
                    }, self::$attrGrp);
                }, [
                    'class' => 'tab-pane fade',
                    'id'    => 'publication-fieldset'
                ])
                ->token(
                    empty($this->content[ 'id' ])
                    ? 'token_node'
                    : 'token_node_' . $this->content[ 'id' ]
                )
                ->group('actions-group', 'fieldset', function ($form) {
                    $form->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ])
                    ->html('cancel', '<button:attr>:_content</button>', [
                        '_content' => t('Cancel'),
                        'class'    => 'btn btn-danger',
                        'onclick'  => 'javascript:history.back();',
                        'type'     => 'button'
                    ]);
                }, self::$attrGrp);
    }

    public function actionsEntitySubmit()
    {
        return $this->token('token_entity')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ])
                ->html('cancel', '<button:attr>:_content</button>', [
                    '_content' => t('Cancel'),
                    'class'    => 'btn btn-danger',
                    'onclick'  => 'javascript:history.back();',
                    'type'     => 'button'
                ]);
    }

    public function rules(&$value)
    {
        $value[ 'attr' ] = [];
        if (preg_match('/^(.*\|)?required(\|.*)?/', $value[ 'field_rules' ])) {
            $value[ 'attr' ][ 'required' ] = 1;
        }
        if (preg_match('/[\|]?(max|max_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $value[ 'field_rules' ], $matches)) {
            if (in_array($value[ 'field_type' ], self::$fieldRules)) {
                $value[ 'attr' ][ 'maxlength' ] = (int) $matches[ 2 ];
            } elseif (in_array($value[ 'field_type' ], [ 'number', 'date', 'one_to_many' ])) {
                $value[ 'attr' ][ 'max' ] = (int) $matches[ 2 ];
            }
        }
        if (preg_match('/[\|]?(min|min_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $value[ 'field_rules' ], $matches)) {
            if (in_array($value[ 'field_type' ], self::$fieldRules)) {
                $value[ 'attr' ][ 'minlength' ] = (int) $matches[ 2 ];
            } elseif (in_array($value[ 'field_type' ], [ 'number', 'date', 'one_to_many' ])) {
                $value[ 'attr' ][ 'min' ] = (int) $matches[ 2 ];
            }
        }
    }
    
    protected function getDateCreated()
    {
        if (empty($this->content[ 'date_created' ])) {
            return date('Y-m-d H:i:s', time());
        }

        return is_numeric($this->content[ 'date_created' ])
            ? date('Y-m-d H:i:s', (int) $this->content[ 'date_created' ])
            : $this->content[ 'date_created' ];
    }
}
