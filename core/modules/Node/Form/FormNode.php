<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Form;

use Soosyze\Components\Form\FormGroupBuilder;
use Soosyze\Components\Router\Router;
use Soosyze\Config;
use SoosyzeCore\FileSystem\Services\File;
use SoosyzeCore\Node\Hook\Config as HookConfig;
use SoosyzeCore\Node\Model\Field\CheckboxOption;
use SoosyzeCore\Node\Model\Field\OneToManyOption;
use SoosyzeCore\Node\Model\Field\RadioOption;
use SoosyzeCore\Node\Model\Field\SelectOption;
use SoosyzeCore\QueryBuilder\Services\Query;

/**
 * @phpstan-import-type NodeStatusEntity from \SoosyzeCore\Node\Extend
 */
class FormNode extends \Soosyze\Components\Form\FormBuilder
{
    /**
     * @var array
     */
    protected $values = [
        'date_created'     => '',
        'id'               => null,
        'meta_description' => '',
        'meta_noarchive'   => false,
        'meta_nofollow'    => false,
        'meta_noindex'     => false,
        'meta_title'       => '',
        'node_status_id'   => 3,
        'sticky'           => false,
        'title'            => '',
        'user_id'          => null
    ];

    /**
     * @var array
     */
    private static $fieldRules = [
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

    /**
     * @var array
     */
    private static $attrGrp = [ 'class' => 'form-group' ];

    /**
     * @var array
     */
    private static $attrGrpInline = [ 'class' => 'form-group-inline' ];

    /**
     * @var File
     */
    private $file;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Config
     */
    private $config;

    /**
     * Les données de l'utilisateur possèdant le contenu.
     *
     * @var array|null
     */
    private $userCurrent = null;

    /**
     * Si l'utilisateur du contenu est modifiable.
     *
     * @var bool
     */
    private $isDisabledUserCurrent = true;

    public function __construct(
        array $attr,
        File $file,
        Query $query,
        Router $router,
        Config $config
    ) {
        parent::__construct($attr + [ 'class' => 'form-api' ]);
        $this->file   = $file;
        $this->query  = $query;
        $this->router = $router;
        $this->config = $config;
    }

    public function setFields(array $fields): self
    {
        $this->fields  = $fields;

        return $this;
    }

    public function makeFields(): self
    {
        return $this
                ->nodeFieldset()
                ->seoFieldset()
                ->userFieldset()
                ->publicationFieldset();
    }

    public function setUserCurrent(?array $userCurrent): self
    {
        $this->userCurrent = $userCurrent;

        return $this;
    }

    public function setDisabledUserCurrent(bool $disabled): self
    {
        $this->isDisabledUserCurrent = $disabled;

        return $this;
    }

    public function nodeFieldset(): self
    {
        return $this->group('fields-fieldset', 'fieldset', function ($form) {
            $form->group('title-group', 'div', function ($form) {
                $form->label('title-label', t('Title of the content'))
                        ->text('title', [
                            'class'     => 'form-control',
                            'maxlength' => 255,
                            'required'  => 1,
                            'value'     => $this->values[ 'title' ]
                    ]);
            }, self::$attrGrp);
            foreach ($this->fields as $value) {
                $key = $value[ 'field_name' ];

                /* Si le contenu du champ n'existe pas alors il est déclaré vide. */
                $this->values[ $key ] = $this->values[ $key ] ?? '';
                $this->makeField($form, $value);
            }
        }, [
                'class' => 'tab-pane active fade',
                'id'    => 'fields-fieldset'
        ]);
    }

    public function entityFieldset(): self
    {
        return $this->group('fields-fieldset', 'fieldset', function ($form) {
            foreach ($this->fields as $value) {
                $key                   = $value[ 'field_name' ];
                /* Si le contenu du champ n'existe pas alors il est déclaré vide. */
                $this->values[ $key ] = isset($this->values[ $key ])
                        ? $this->values[ $key ]
                        : '';
                $this->makeField($form, $value);
            }
        }, [
                'class' => 'tab-pane active fade',
                'id'    => 'fields-fieldset'
        ]);
    }

    public function makeField(FormGroupBuilder &$form, array $value): FormGroupBuilder
    {
        $key = $value[ 'field_name' ];
        $this->rules($value);

        return $form->group("$key-group", 'div', function ($form) use ($value, $key) {
            switch ($value[ 'field_type' ]) {
                    case 'checkbox':
                        $this->makeCheckbox($form, $key, $value, CheckboxOption::createFromJson($value[ 'field_option' ]));

                        break;
                    case 'file':
                    case 'image':
                        $form->label("$key-label", t($value[ 'field_label' ]), [
                            'data-tooltip' => t($value[ 'field_description' ])
                        ]);
                        $this->file->inputFile($key, $form, $this->values[ $key ], $value[ 'field_type' ]);

                        break;
                    case 'one_to_many':
                        $this->makeOneToMany($form, $key, $value, OneToManyOption::createFromJson($value[ 'field_option' ]));

                        break;
                    case 'radio':
                        $this->makeRadio($form, $key, $value, RadioOption::createFromJson($value[ 'field_option' ]));

                        break;
                    case 'select':
                        $this->makeSelect($form, $key, $value, SelectOption::createFromJson($value[ 'field_option' ]));

                        break;
                    case 'textarea':
                        $this->makeTextarea($form, $key, $value);

                        break;
                    case 'number':
                        $this->makeNumber($form, $key, $value);

                        break;
                    default:
                        $this->makeInput($form, $key, $value);

                        break;
                }
        }, self::$attrGrp);
    }

    public function makeNumber(FormGroupBuilder &$form, string $key, array $value): void
    {
        $default = $this->values[ $key ] ?? $value[ 'field_default_value' ];

        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ]),
                'for'          => $key,
                'required'     => !empty($value[ 'attr' ][ 'required' ])
            ])
            ->group("$key-flex", 'div', function ($form) use ($key, $value, $default) {
                $form->number($key, [
                    ':actions' => 1,
                    'class'    => 'form-control',
                    'value'    => (int) $default
                    ] + $value[ 'attr' ]);
            }, [ 'class' => 'form-group-flex' ]);
    }

    public function makeInput(FormGroupBuilder &$form, string $key, array $value): void
    {
        $type    = $value[ 'field_type' ];
        $default = $this->values[ $key ] ?? $value[ 'field_default_value' ];

        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ])
            ])
            ->$type($key, [
                'class' => 'form-control',
                'value' => $default
                ] + $value[ 'attr' ]);
    }

    public function makeCheckbox(FormGroupBuilder &$form, string $key, array $value, CheckboxOption $checkboxOption): void
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
            'data-tooltip' => t($value[ 'field_description' ]),
            'required'     => isset($value[ 'attr' ][ 'required' ]),
        ]);
        foreach ($checkboxOption->getOptions() as $keyCheckbox => $option) {
            $form->group("$keyCheckbox-group", 'div', function ($form) use ($key, $keyCheckbox, $option) {
                $form->checkbox("{$key}[$keyCheckbox]", [
                        'id'      => "$key-$keyCheckbox",
                        'checked' => in_array($keyCheckbox, explode(',', $this->values[ $key ])),
                        'value'   => $keyCheckbox
                    ])
                    ->label("$key-$keyCheckbox-label", '<span class="ui"></span> ' . t($option), [
                        'for' => "$key-$keyCheckbox"
                        ]);
            }, self::$attrGrp);
        }
    }

    public function makeOneToMany(FormGroupBuilder $form, string $key, array $value, OneToManyOption $oneToManyOption): void
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
            'required'     => !empty($value[ 'attr' ][ 'required' ]),
            'data-tooltip' => t($value[ 'field_description' ])
        ]);
        if (!isset($this->values[ 'entity_id' ])) {
            $form->html('content_nothing', '<div:attr><p>:content</p></div>', [
                ':content' => t('Save your content before you can add items'),
                'class'    => 'alert alert-info',
                'style'    => 'cursor:not-allowed'
            ]);

            return;
        }

        $data = $this->query
            ->from($oneToManyOption->getRelationTable())
            ->where($oneToManyOption->getForeignKey(), '=', $this->values[ 'entity_id' ]);

        if ($oneToManyOption->getOrderBy() && $oneToManyOption->getSort()) {
            $data->orderBy($oneToManyOption->getOrderBy(), $oneToManyOption->getSort());
        }

        $subFields = $data->fetchAll();
        $dir       = $this->router->getBasePath();

        $attrSortable = [ 'class' => 'form-group divide-y' ];
        if ($oneToManyOption->getOrderBy() === OneToManyOption::WEIGHT_FIELD) {
            $attrSortable = [
                'class'           => 'form-group divide-y',
                'data-ghostClass' => 'placeholder',
                'data-draggable'  => 'sortable',
                'data-onEnd'      => 'sortEntity',
                'style'           => 'cursor: grab'
            ];
        }

        $form->group("$key-group", 'div', function ($form) use ($key, $subFields, $oneToManyOption, $dir) {
            foreach ($subFields as $field) {
                $idEntity = $field[ "{$key}_id" ];
                $form->group("$key-$idEntity-group", 'div', function ($form) use ($key, $idEntity, $oneToManyOption, $field, $dir) {
                    if ($oneToManyOption->getOrderBy() === OneToManyOption::WEIGHT_FIELD) {
                        $form->html("$key-$idEntity-drag", '<div class="table-width-minimum"><i class="fa fa-arrows-alt-v" aria-hidden="true"></i></div>')
                            ->hidden("{$key}[$idEntity][weight]", [
                                'value' => $field[ OneToManyOption::WEIGHT_FIELD ]
                            ])->hidden("{$key}[$idEntity][id]", [
                            'value' => $idEntity
                        ]);
                    }

                    $content = $field[ $oneToManyOption->getFieldShow()];
                    if ($this->isShowFile($oneToManyOption, $field)) {
                        $src     = $dir . '/' . $field[ $oneToManyOption->getFieldTypeShow() ];
                        $content = "<img alt='$content' src='$src' class='img-thumbnail img-thumbnail-light'/>";
                    }

                    $form->html("$key-$idEntity-show", '<div class="table-min-width-100"><a:attr>:content</a></div>', [
                            ':content' => $content,
                            'href'     => $this->router->generateUrl('node.entity.edit', [
                                'idNode'   => $this->values[ 'id' ],
                                'entity'   => $key,
                                'idEntity' => $field[ "{$key}_id" ]
                            ]),
                        ])
                        ->group("$key-$idEntity-actions", 'div', function ($form) use ($field, $idEntity, $key) {
                            $form->html("$key-$idEntity-edit", '<a:attr>:content</a>', [
                                ':content' => '<i class="fa fa-edit" aria-hidden="true"></i> ' . t('Edit'),
                                'class'    => 'btn',
                                'href'     => $this->router->generateUrl('node.entity.edit', [
                                    'idNode'   => $this->values[ 'id' ],
                                    'entity'   => $key,
                                    'idEntity' => $field[ "{$key}_id" ]
                                ]),
                            ])
                            ->html("$key-$idEntity-delete", '<a:attr>:content</a>', [
                                ':content' => '<i class="fa fa-times" aria-hidden="true"></i> ' . t('Delete'),
                                'class'    => 'btn',
                                'href'     => $this->router->generateUrl('node.entity.delete', [
                                    'idNode'   => $this->values[ 'id' ],
                                    'entity'   => $key,
                                    'idEntity' => $field[ "{$key}_id" ]
                                ]),
                            ]);
                        }, [ 'class' => 'table-width-300' ]);
                }, [ 'class' => 'sort_weight nestable-body table-row' ]);
            }
        }, $attrSortable);

        if (!isset($value[ 'attr' ][ 'max' ]) || $value[ 'attr' ][ 'max' ] > count($subFields)) {
            $form->group("add-$key-group", 'div', function ($form) use ($key) {
                $form->html('add-' . $key, '<a:attr>:content</a>', [
                    ':content' => '<i class="fa fa-plus" aria-hidden="true"></i> ' . t('Add content'),
                    'class'    => 'btn btn-primary',
                    'href'     => $this->router->generateUrl('node.entity.create', [
                        'idNode' => $this->values[ 'id' ],
                        'entity'  => $key,
                    ])
                ]);
            });
        }
    }

    public function isShowFile(OneToManyOption $oneToManyOption, array $field): bool
    {
        return $oneToManyOption->getFieldTypeShow() === 'image' && is_file($field[ $oneToManyOption->getFieldTypeShow() ]);
    }

    public function makeRadio(FormGroupBuilder &$form, string $key, array $value, RadioOption $radioOption): void
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
            'data-tooltip' => t($value[ 'field_description' ]),
            'required'     => isset($value[ 'attr' ][ 'required' ])
        ]);
        foreach ($radioOption->getOptions() as $keyRadio => $option) {
            $form->group("$keyRadio-group", 'div', function ($form) use ($key, $keyRadio, $option) {
                $form->radio($key, [
                        'id'      => "$key-$keyRadio",
                        'checked' => $this->values[ $key ] == $keyRadio,
                        'value'   => $keyRadio
                        ])
                    ->label("$key-$keyRadio-label", '<span class="ui"></span> ' . t($option), [
                        'for' => "$key-$keyRadio"
                ]);
            }, self::$attrGrp);
        }
    }

    public function makeSelect(FormGroupBuilder &$form, string $key, array $value, SelectOption $selectOption): void
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ])
            ])
            ->select($key, $selectOption->getOptions(), [
                ':selected' => $this->values[ $key ],
                'class'     => 'form-control'
                ] + $value[ 'attr' ]);
    }

    public function makeTextarea(FormGroupBuilder &$form, string $key, array $value): void
    {
        $form->label("$key-label", t($value[ 'field_label' ]), [
                'data-tooltip' => t($value[ 'field_description' ])
            ])
            ->textarea($key, $this->values[ $key ], [
                'class' => 'form-control editor',
                'rows'  => 8
                ] + $value[ 'attr' ]);
    }

    public function titleGroup(): self
    {
        return $this->group('title-group', 'div', function ($form) {
            $form->label('title-label', t('Title of the content'))
                    ->text('title', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'required'    => 1,
                        'placeholder' => t('Title of the content'),
                        'value'       => $this->values[ 'title' ]
                ]);
        }, self::$attrGrp);
    }

    public function userFieldset(): self
    {
        $options = [];
        if ($this->userCurrent) {
            $options[] = [
                'label' => $this->userCurrent[ 'username' ],
                'value' => $this->userCurrent[ 'user_id' ]
            ];
        } elseif ($this->values[ 'user_id' ]) {
            $user = $this->query
                ->from('user')
                ->where('user_id', '==', $this->values[ 'user_id' ])
                ->fetch();

            if ($user !== null) {
                $options[] = [
                    'label' => $user[ 'username' ],
                    'value' => $this->values[ 'user_id' ]
                ];
            }
        }

        return $this->group('user-fieldset', 'fieldset', function ($form) use ($options) {
            $form->legend('user-legend', t('User'))
                    ->group('user_id-group', 'div', function ($form) use ($options) {
                        $form->label('user_id-label', t('User'), [
                            'data-tooltip' => $this->isDisabledUserCurrent
                            ? t('You do not have the right to modify the user of the content')
                            : ''
                        ])
                        ->select('user_id', $options, [
                            ':selected'        => $this->values[ 'user_id' ],
                            'class'            => 'form-control select-ajax',
                            'data-placeholder' => t('Anonymous'),
                            'data-link'        => $this->router->generateUrl('user.api.select'),
                            'disabled'         => $this->isDisabledUserCurrent,
                        ]);
                    }, self::$attrGrp);
        }, [
                'class' => 'tab-pane fade',
                'id'    => 'user-fieldset'
        ]);
    }

    public function seoFieldset(): self
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
                            'value'       => $this->values[ 'meta_title' ]
                        ])
                        ->html('meta_title-info', '<p>:content</p>', [
                            ':content' => t('Variables allowed') . ' <code>:page_title</code>, <code>:site_title</code>, <code>:site_description</code>'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_description-group', 'div', function ($form) {
                        $form->label('meta_description-label', t('Description'), [
                            'data-tooltip' => t('Leave blank to use the default site description')
                        ])
                        ->textarea('meta_description', $this->values[ 'meta_description' ], [
                            'class' => 'form-control',
                            'rows'  => 3
                        ])
                        ->html('meta_description-info', '<p>:content</p>', [
                            ':content' => t('Variables allowed') . ' <code>:page_title</code>, <code>:site_title</code>, <code>:site_description</code>'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_noindex-group', 'div', function ($form) {
                        $form->checkbox('meta_noindex', [ 'checked' => $this->values[ 'meta_noindex' ] ])
                        ->label('meta_noindex-label', '<span class="ui"></span> ' . t('Block indexing') . ' <code>noindex</code>', [
                            'for' => 'meta_noindex'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_nofollow-group', 'div', function ($form) {
                        $form->checkbox('meta_nofollow', [ 'checked' => $this->values[ 'meta_nofollow' ] ])
                        ->label('meta_nofollow-label', '<span class="ui"></span> ' . t('Block link tracking') . ' <code>nofollow</code>', [
                            'for' => 'meta_nofollow'
                        ]);
                    }, self::$attrGrp)
                    ->group('meta_noarchive-group', 'div', function ($form) {
                        $form->checkbox('meta_noarchive', [ 'checked' => $this->values[ 'meta_noarchive' ] ])
                        ->label('meta_noarchive-label', '<span class="ui"></span> ' . t('Block caching') . ' <code>noarchive</code>', [
                            'for' => 'meta_noarchive'
                        ]);
                    }, self::$attrGrp);
        }, [
                'class' => 'tab-pane fade',
                'id'    => 'seo-fieldset'
        ]);
    }

    public function publicationFieldset(): self
    {
        return $this
                ->group('publication-fieldset', 'fieldset', function ($form) {
                    $form
                    ->legend('publication-legend', t('Publication'))
                    ->label('date_created-label', t('Publication status'))
                    ->group('node_status-group', 'div', function ($form) {
                        $this->query->from('node_status');
                        if (!$this->config->get('settings.node_cron', HookConfig::CRON)) {
                            $this->query->where('node_status_id', '!=', 2);
                        }
                        /** @phpstan-var array<NodeStatusEntity> $status */
                        $status = $this->query->fetchAll();
                        foreach ($status as $value) {
                            $form->group("node_status_id-{$value[ 'node_status_id' ]}-group", 'div', function ($form) use ($value) {
                                $form->radio('node_status_id', [
                                    'id'      => "node_status_id-{$value[ 'node_status_id' ]}",
                                    'checked' => $this->values[ 'node_status_id' ] == $value[ 'node_status_id' ],
                                    'value'   => $value[ 'node_status_id' ]
                                ])
                                ->label('node_status_id-label', t($value[ 'node_status_name' ]), [
                                    'for'    => "node_status_id-{$value[ 'node_status_id' ]}"
                                ]);
                            }, self::$attrGrpInline);
                        }
                    }, [ 'class' => 'form-group btn-group' ])
                    ->group('date_created-group', 'div', function ($form) {
                        $form->label('date_created-label', t('Publication date'), [
                            'data-tooltip' => t('Leave blank to use the form submission date. It must be less than or equal to today\'s date')
                        ])
                        ->group('date_created-flex', 'div', function ($form) {
                            $form->date('date', [
                                'class' => 'form-control',
                                'value' => $this->getDateCreated()
                            ])
                            ->time('date_time', [
                                'class' => 'form-control',
                                'value' => $this->getDateTimeCreated()
                            ]);
                        }, [ 'class' => 'form-group-flex' ]);
                    }, self::$attrGrp)
                    ->group('sticky-group', 'div', function ($form) {
                        $form->checkbox('sticky', [ 'checked' => $this->values[ 'sticky' ] ])
                        ->label('sticky-label', '<span class="ui"></span> <i class="fa fa-thumbtack" aria-hidden="true"></i> ' . t('Pin content'), [
                            'for' => 'sticky'
                        ]);
                    }, self::$attrGrp);
                }, [
                    'class' => 'tab-pane fade',
                    'id'    => 'publication-fieldset'
                ])
                ->token(
                    empty($this->values[ 'id' ])
                    ? 'token_node'
                    : 'token_node_' . $this->values[ 'id' ]
                )
                ->group('submit-group', 'div', function ($form) {
                    $form->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ])
                    ->button('cancel', t('Cancel'), [
                        'class'    => 'btn btn-default',
                        'onclick'  => 'javascript:history.back();'
                    ]);
                }, self::$attrGrp);
    }

    public function actionsEntitySubmit(): self
    {
        return $this
                ->group('submit-group', 'div', function ($form) {
                    $form->token('token_entity')
                    ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ])
                    ->button('cancel', t('Cancel'), [
                        'class'    => 'btn btn-default',
                        'onclick'  => 'javascript:history.back();'
                    ]);
                }, self::$attrGrp);
    }

    public function rules(array &$value): void
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

    private function getDateCreated(): string
    {
        if (empty($this->values[ 'date_created' ])) {
            $time = time();
        } elseif (is_numeric($this->values[ 'date_created' ])) {
            $time = (int) $this->values[ 'date_created' ];
        } else {
            $time = strtotime($this->values[ 'date_created' ]);
        }

        return date('Y-m-d', $time);
    }

    private function getDateTimeCreated(): string
    {
        if (empty($this->values[ 'date_created' ])) {
            $time = time();
        } elseif (is_numeric($this->values[ 'date_created' ])) {
            $time = (int) $this->values[ 'date_created' ];
        } else {
            $time = strtotime($this->values[ 'date_created' ]);
        }

        return date('H:i', $time);
    }
}
