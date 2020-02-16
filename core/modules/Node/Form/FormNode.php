<?php

namespace SoosyzeCore\Node\Form;

use Soosyze\Components\Form\FormBuilder;

class FormNode extends FormBuilder
{
    protected $content = [
        'title'          => '',
        'meta_noindex'   => false,
        'meta_nofollow'  => false,
        'meta_noarchive' => false
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

    public function __construct(array $attributes, $file = null)
    {
        parent::__construct($attributes);
        $this->file = $file;
    }

    public function content($content, $type, $query)
    {
        $this->content = array_merge($this->content, $content);
        $this->type    = $type;
        $this->query   = $query;

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
            foreach ($this->query as $value) {
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
                    ? json_decode($value[ 'field_option' ])
                    : [];
            switch ($value[ 'field_type' ]) {
                    case 'textarea':
                        $form->label("$key-label", t($value[ 'field_label' ]))
                            ->textarea($key, $this->content[ $key ], [
                                'class'       => 'form-control editor',
                                'rows'        => 8,
                                'placeholder' => t($value[ 'field_label' ])
                                ] + $value[ 'attr' ]);

                        break;
                    case 'select':
                        $this->makeSelect($form, $key, $value, $options);

                        break;
                    case 'radio':
                        $this->makeRadio($form, $key, $value, $options);

                        break;
                    case 'checkbox':
                        $this->makeCheckbox($form, $key, $value, $options);

                        break;
                    case 'image':
                    case 'file':
                        $form->label("$key-label", t($value[ 'field_label' ]));
                        $this->file->inputFile($key, $form, $this->content[ $key ], $value[ 'field_type' ]);

                        break;
                    default:
                        $type = $value[ 'field_type' ];
                        $form->label("$key-label", t($value[ 'field_label' ]))
                            ->$type($key, [
                                'class'       => 'form-control',
                                'placeholder' => t($value[ 'field_label' ]),
                                'value'       => $this->content[ $key ]
                                ] + $value[ 'attr' ]);

                        break;
                }
        }, self::$attrGrp);
    }

    public function makeRadio(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]));
        foreach ($options as $key_radio => $option) {
            $form->group("$key_radio-group", 'div', function ($form) use ($key, $key_radio, $option) {
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

    public function makeCheckbox(&$form, $key, $value, $options)
    {
        $form->label("$key-label", t($value[ 'field_label' ]));
        foreach ($options as $key_radio => $option) {
            $form->group("$key_radio-group", 'div', function ($form) use ($key, $key_radio, $option) {
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

    public function makeSelect(&$form, $key, $value, $options)
    {
        $select_otions = [];
        foreach ($options as $key_radio => $option) {
            $select_otions[ $key_radio ] = [ 'label' => $option, 'value' => $key_radio ];
            if ($key_radio == $this->content[ $key ]) {
                $select_otions[ $key_radio ][ 'selected' ] = 1;
            }
        }

        $form->label("$key-label", t($value[ 'field_label' ]))
            ->select($key, $select_otions, [ 'class' => 'form-control' ] + $value[ 'attr' ]);
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

    public function rules(&$value)
    {
        $value[ 'attr' ] = [];
        if (preg_match('/^(.*\|)?required(\|.*)?/', $value[ 'field_rules' ])) {
            $value[ 'attr' ][ 'required' ] = 1;
        }
        if (preg_match('/[\|]?(max|max_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $value[ 'field_rules' ], $matches)) {
            if (in_array($value[ 'field_type' ], self::$field_rules)) {
                $value[ 'attr' ][ 'maxlength' ] = $matches[ 1 ];
            } elseif (in_array($value[ 'field_type' ], [ 'number', 'date' ])) {
                $value[ 'attr' ][ 'max' ] = $matches[ 1 ];
            }
        }
        if (preg_match('/[\|]?(min|min_numeric):(\d+)(yb|zb|eb|pb|tb|gb|mb|kb|b)?/', $value[ 'field_rules' ], $matches)) {
            if (in_array($value[ 'field_type' ], self::$field_rules)) {
                $value[ 'attr' ][ 'minlength' ] = $matches[ 1 ];
            } elseif (in_array($value[ 'field_type' ], [ 'number', 'date' ])) {
                $value[ 'attr' ][ 'min' ] = $matches[ 1 ];
            }
        }
    }
}
