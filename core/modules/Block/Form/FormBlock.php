<?php

declare(strict_types=1);

namespace SoosyzeCore\Block\Form;

class FormBlock extends \Soosyze\Components\Form\FormBuilder
{
    public const HIDE_BLOCK_PAGES = 0;

    public const SHOW_BLOCK_PAGES = 1;

    public const HIDE_BLOCK_ROLES = 0;

    public const SHOW_BLOCK_ROLES = 1;

    /**
     * @var array
     */
    protected $values = [
        'block_id'         => null,
        'class'            => '',
        'content'          => '',
        'is_title'         => true,
        'key_block'        => '',
        'pages'            => 'user/%',
        'roles'            => [ 1, 2 ],
        'section'          => '',
        'theme'            => 'public',
        'title'            => '',
        'visibility_pages' => false,
        'visibility_roles' => true,
        'weight'           => 1
    ];

    /**
     * @var array
     */
    private static $attrGrp = [ 'class' => 'form-group' ];

    /**
     * @var array
     */
    private $rolesUser = [];

    public function setRoles(array $rolesUser): self
    {
        $this->rolesUser = $rolesUser;

        return $this;
    }

    public function makeFields(): self
    {
        $this->group('block-fieldset', 'fieldset', function ($form) {
            $form->group('title-group', 'div', function ($form) {
                $form->label('title-label', t('Title'), [
                        'data-tooltip' => t('Le titre est obligatoire pour l\'administration, vous pouvez choisir de l\'afficher/cacher pour vos visiteurs')
                    ])
                    ->text('title', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'required'  => true,
                        'value'     => $this->values[ 'title' ]
                    ]);
            }, self::$attrGrp)
                ->group('is_title-group', 'div', function ($form) {
                    $form->checkbox('is_title', [
                        'checked' => $this->values[ 'is_title' ]
                    ])
                    ->label(
                        'is_title-label',
                        '<span class="ui"></span>' . t('Afficher le titre'),
                        [ 'for' => 'is_title' ]
                    );
                }, self::$attrGrp);
            if (empty($this->values[ 'hook' ])) {
                $form->group('content-group', 'div', function ($form) {
                    $form->label('content-label', t('Content'), [
                            'for' => 'content'
                        ])
                        ->textarea('content', (string) $this->values[ 'content' ], [
                            'class'       => 'form-control editor',
                            'placeholder' => '<p>Hello World!</p>',
                            'rows'        => 8
                        ]);
                }, self::$attrGrp);
            }
        }, [
                'class' => 'tab-pane active fade',
                'id'    => 'block-fieldset'
            ])
            ->group('page-fieldset', 'fieldset', function ($form) {
                $form->legend('page-legend', t('Visibility by pages'))
                ->group('visibility_pages_1-group', 'div', function ($form) {
                    $form->radio('visibility_pages', [
                        'checked'  => !$this->values[ 'visibility_pages' ],
                        'id'       => 'visibility_pages_1',
                        'required' => 1,
                        'value'    => self::HIDE_BLOCK_PAGES
                    ])->label(
                        'visibility_pages-label',
                        '<i class="fa fa-eye-slash" aria-hidden="true"></i> ' . t('Hide the block on the pages listed'),
                        ['for' => 'visibility_pages_1']
                    );
                }, self::$attrGrp)
                ->group('visibility_pages_2-group', 'div', function ($form) {
                    $form->radio('visibility_pages', [
                        'checked'  => $this->values[ 'visibility_pages' ],
                        'id'       => 'visibility_pages_2',
                        'required' => 1,
                        'value'    => self::SHOW_BLOCK_PAGES,
                    ])->label(
                        'visibility_pages-label',
                        '<i class="fa fa-eye" aria-hidden="true"></i> ' . t('Show block on listed pages'),
                        [ 'for' => 'visibility_pages_2' ]
                    );
                }, self::$attrGrp)
                ->group('pages-group', 'div', function ($form) {
                    $form->label('pages-label', t('List of pages'), [
                        'data-tooltip' => t('Enter a path by line. The "%" character is a wildcard character that specifies all characters.')
                    ])
                    ->textarea('pages', $this->values[ 'pages' ], [
                        'class'       => 'form-control',
                        'placeholder' => 'admin/%' . PHP_EOL . 'user/%',
                        'rows'        => 5
                    ])
                    ->html('info-variable_allowed', '<p>:content</p>', [
                        ':content' => t('Variables allowed') . ' <code>%</code>'
                    ]);
                }, self::$attrGrp);
            }, [
                'class' => 'tab-pane fade',
                'id'    => 'page-fieldset'
            ])
            ->group('roles-fieldset', 'fieldset', function ($form) {
                $form->legend('roles-legend', t('Visibility by roles'))
                ->group('visibility_roles_1-group', 'div', function ($form) {
                    $form->radio('visibility_roles', [
                        'checked'  => !$this->values[ 'visibility_roles' ],
                        'id'       => 'visibility_roles_1',
                        'required' => 1,
                        'value'    => self::HIDE_BLOCK_ROLES
                    ])->label(
                        'visibility_roles-label',
                        '<i class="fa fa-eye-slash" aria-hidden="true"></i> ' . t('Hide the block to selected roles'),
                        [ 'for' => 'visibility_roles_1' ]
                    );
                }, self::$attrGrp)
                ->group('visibility_roles_2-group', 'div', function ($form) {
                    $form->radio('visibility_roles', [
                        'checked'  => $this->values[ 'visibility_roles' ],
                        'id'       => 'visibility_roles_2',
                        'required' => 1,
                        'value'    => self::SHOW_BLOCK_ROLES
                    ])->label(
                        'visibility_roles-label',
                        '<i class="fa fa-eye" aria-hidden="true"></i> ' . t('Show block with selected roles'),
                        [ 'for' => 'visibility_roles_2' ]
                    );
                }, self::$attrGrp);
                foreach ($this->rolesUser as $role) {
                    $form->group("role_{$role[ 'role_id' ]}-group", 'div', function ($form) use ($role) {
                        $form->checkbox("roles[{$role[ 'role_id' ]}]", [
                            'checked' => in_array($role[ 'role_id' ], $this->values[ 'roles' ]),
                            'id'      => "role_{$role[ 'role_id' ]}",
                            'value'   => $role[ 'role_label' ]
                        ])
                        ->label(
                            "role_{$role[ 'role_id' ]}-label",
                            $this->getLabelRole($role),
                            [ 'for' => "role_{$role[ 'role_id' ]}" ]
                        );
                    }, self::$attrGrp);
                }
            }, [
                'class' => 'tab-pane fade',
                'id'    => 'roles-fieldset'
            ])
            ->group('advanced-fieldset', 'fieldset', function ($form) {
                $form->legend('advanced-legend', t('Advanced'))
                ->group('class-group', 'div', function ($form) {
                    $form->label('class-label', t('Class CSS'))
                    ->text('class', [
                        'class'       => 'form-control',
                        'placeholder' => 'text-center',
                        'value'       => $this->values[ 'class' ]
                    ]);
                }, self::$attrGrp);
            }, [
                'class' => 'tab-pane fade',
                'id'    => 'advanced-fieldset'
            ])
            ->group('submit-group', 'div', function ($form) {
                $form->token($this->getTokenName())
                ->hidden('key_block', [ 'value' => $this->values[ 'key_block' ] ])
                ->hidden('theme', [ 'value' => $this->values[ 'theme' ] ])
                ->hidden('section', [ 'value' => $this->values[ 'section' ] ])
                ->hidden('weight', [ 'value' => $this->values[ 'weight' ] ])
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
            });

        return $this;
    }

    private function getLabelRole(array $role): string
    {
        return '<span class="ui"></span>'
            . '<span class="badge-role" style="background-color: ' . $role[ 'role_color' ] . '">'
            . '<i class="' . $role[ 'role_icon' ] . '" aria-hidden="true"></i>'
            . '</span> '
            . t($role[ 'role_label' ]);
    }

    private function getTokenName(): string
    {
        return $this->values['block_id'] === null
            ? 'token_block_create'
            : "token_block_edit_{$this->values['block_id']}";
    }
}
