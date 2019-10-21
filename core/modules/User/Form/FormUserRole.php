<?php

namespace SoosyzeCore\User\Form;

use Soosyze\Components\Form\FormBuilder;

class FormUserRole extends FormBuilder
{
    protected $content = [
        'role_label'       => '',
        'role_description' => '',
        'role_weight'      => 1,
        'role_color'       => '#e6e7f4',
        'role_icon'        => 'fa fa-user'
    ];

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function content($content)
    {
        $this->content = array_merge($this->content, $content);

        return $this;
    }

    public function labelRole(&$form)
    {
        $form->group('role-label-group', 'div', function ($form) {
            $form->label('role-label-label', t('Name'))
                ->text('role_label', [
                    'class'       => 'form-control',
                    'maxlength'   => 254,
                    'required'    => 1,
                    'value'       => $this->content[ 'role_label' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function description(&$form)
    {
        $form->group('role-description-group', 'div', function ($form) {
            $form->label('role-description-label', t('Description'))
                ->text('role_description', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $this->content[ 'role_description' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function weight(&$form)
    {
        $form->group('role-weight-group', 'div', function ($form) {
            $form->label('role-weight-label', t('Weight'))
                ->number('role_weight', [
                    'class' => 'form-control',
                    'max'   => 50,
                    'min'   => 1,
                    'value' => $this->content[ 'role_weight' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function colorRole(&$form)
    {
        $form->group('role-color-group', 'div', function ($form) {
            $form->label('role-color-label', t('Color'), ['for' => 'role_color'])
                ->group('role-color-group', 'div', function ($form) {
                    $form->text('role_color', [
                        'class'   => 'form-control',
                        'pattern' => '#([a-fA-F0-9]{6})',
                        'value'   => $this->content[ 'role_color' ]
                    ])
                    ->html('btn-color', '<button:css:attr>:_content</button>', [
                        '_content' => '<i class="fa fa-sync"></i>',
                        'aria-label' => t('Random color'),
                        'class'    => 'btn',
                        'id'       => 'role_color_btn',
                        'style'    => 'background-color:' . $this->content[ 'role_color' ],
                        'onclick'  => 'randomColor = getRandomColor();'
                        . 'document.getElementById(\'role_color\').value = randomColor;'
                        . 'document.getElementById(\'role_color_btn\').style.background = randomColor;',
                        'type'     => 'button',
                        'data-tooltip' => t('Random color')
                    ]);
                }, [ 'class' => 'form-group-flex' ]);
        }, self::$attrGrp);

        return $this;
    }
    
    public function icon(&$form)
    {
        $form->group('role-icon-group', 'div', function ($form) {
            $form->label('menu-icon-label', t('Icon'), [
                        'data-tooltip' => t('Icons are created from the CSS class of FontAwesome')
                    ])
                ->group('role-color-group', 'div', function ($form) {
                    $form->text('role_icon', [
                        'class'       => 'form-control text_icon',
                        'maxlength'   => 255,
                        'placeholder' => 'fa fa-home',
                        'value'       => $this->content[ 'role_icon' ],
                    ])->html('btn-color', '<button:css:attr>:_content</button>', [
                        '_content'     => '<i class="' . $this->content[ 'role_icon' ] . '" aria-hidden="true"></i>',
                        'aria-label'   => t('Rendering'),
                        'class'        => 'btn render_icon',
                        'type'         => 'button',
                        'data-tooltip' => t('Rendering')
                    ]);
                }, [ 'class' => 'form-group-flex' ]);
        }, self::$attrGrp);

        return $this;
    }

    public function generate()
    {
        return $this->group('role-fieldset', 'fieldset', function ($form) {
            $form->legend('role-legend', t('Role overview'));
            $this->labelRole($form)
                    ->description($form)
                    ->weight($form)
                    ->colorRole($form)
                    ->icon($form);
        })->submitForm();
    }
    
    public function submitForm()
    {
        return $this->html('cancel', '<button:css:attr>:_content</button>', [
                    '_content' => t('Cancel'),
                    'class'    => 'btn btn-danger',
                    'onclick'  => 'javascript:history.back();',
                    'type'     => 'button'
                ])
                ->token('token_role_submit')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
    }
    
    public function generateDelete()
    {
        return $this->group('user-edit-information-fieldset', 'fieldset', function ($form) {
            $form->legend('user-edit-information-legend', t('Delete role'))
                    ->html('system-favicon-info-dimensions', '<p:css:attr>:_content</p>', [
                        '_content' => t('Warning ! The deletion of the role is final.')
                    ]);
        })
                ->token('token_role_delete')
                ->submit('sumbit', t('Delete'), [ 'class' => 'btn btn-danger' ]);
    }
}
