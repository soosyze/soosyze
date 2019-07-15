<?php

namespace SoosyzeCore\User\Form;

use Soosyze\Components\Form\FormBuilder;

class FormUserRole extends FormBuilder
{
    protected $content = [
        'role_label'       => '',
        'role_description' => '',
        'role_weight'      => 1,
        'role_color'       => '#e6e7f4'
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
            $form->label('role-label-label', 'Label')
                ->text('role_label', 'role_label', [
                    'class'       => 'form-control',
                    'maxlength'   => 254,
                    'placeholder' => 'Nom du nouveau rôle',
                    'required'    => 1,
                    'value'       => $this->content[ 'role_label' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function description(&$form)
    {
        $form->group('role-description-group', 'div', function ($form) {
            $form->label('role-description-label', 'Description')
                ->text('role_description', 'role_description', [
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
            $form->label('role-weight-label', 'Poids')
                ->number('role_weight', 'role_weight', [
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
            $form->label('role-color-label', 'Color')
                ->text('role_color', 'role_color', [
                    'class'   => 'form-control',
                    'pattern' => '#([a-fA-F0-9]{6})',
                    'value'   => $this->content[ 'role_color' ]
                ])->html('btn-color', '<button:css:attr>:_content</button>', [
                '_content' => 'Couleur aléatoire',
                'class'    => 'btn',
                'id'       => 'role_color_btn',
                'style'    => 'background-color:' . $this->content[ 'role_color' ],
                'onclick'  => 'randomColor = getRandomColor();'
                . 'document.getElementById(\'role_color\').value = randomColor;'
                . 'document.getElementById(\'role_color_btn\').style.background = randomColor;',
                'type'     => 'button'
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function generate()
    {
        return $this->group('role-fieldset', 'fieldset', function ($form) {
            $form->legend('role-legend', 'Aperçu du rôle');
            $this->labelRole($form)
                    ->description($form)
                    ->weight($form)
                    ->colorRole($form);
        })->submitForm();
    }
    
    public function submitForm()
    {
        return $this->html('cancel', '<button:css:attr>:_content</button>', [
                    '_content' => 'Annuler',
                    'class'    => 'btn btn-danger',
                    'onclick'  => 'javascript:history.back();',
                    'type'     => 'button'
                ])
                ->token('token_role_submit')
                ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
    }
    
    public function generateDelete()
    {
        return $this->group('user-edit-information-fieldset', 'fieldset', function ($form) {
            $form->legend('user-edit-information-legend', 'Suppression de rôle')
                    ->html('system-favicon-info-dimensions', '<p:css:attr>:_content</p>', [
                        '_content' => 'Attention ! La suppression du rôle est définitif.'
                    ]);
        })
                ->token('token_role_delete')
                ->submit('sumbit', 'Supprimer le rôle', [ 'class' => 'btn btn-danger' ]);
    }
}
