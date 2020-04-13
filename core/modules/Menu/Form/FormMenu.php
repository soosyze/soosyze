<?php

namespace SoosyzeCore\Menu\Form;

class FormMenu extends \Soosyze\Components\Form\FormBuilder
{
    protected $content = [
        'title'       => '',
        'description' => ''
    ];

    public function content($content)
    {
        $this->content = array_merge($this->content, $content);

        return $this;
    }

    public function make()
    {
        $this->group('menu-fieldset', 'fieldset', function ($form) {
            $form->legend('menu-legend', t('Fill in the following fields'))
                ->group('title-group', 'div', function ($form) {
                    $form->label('title-label', t('Menu title'))
                    ->text('title', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => t('Menu perso'),
                        'required'    => 1,
                        'value'       => $this->content[ 'title' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('description-group', 'div', function ($form) {
                    $form->label('description-label', t('Description'))
                    ->textarea('description', $this->content[ 'description' ], [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'required'  => 1
                    ]);
                }, [ 'class' => 'form-group' ]);
        }, [ 'class' => 'form-group' ])
            ->token('token_link_form')
            ->html('cancel', '<button:attr>:_content</button>', [
                '_content' => t('Cancel'),
                'class'    => 'btn btn-danger',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ])
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return $this;
    }
}
