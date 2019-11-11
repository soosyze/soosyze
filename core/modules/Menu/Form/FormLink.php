<?php

namespace SoosyzeCore\Menu\Form;

class FormLink extends \Soosyze\Components\Form\FormBuilder
{
    protected $content = [
        'title_link'  => '',
        'link'        => '',
        'fragment'    => '',
        'icon'        => '',
        'target_link' => ''
    ];

    public function content($content)
    {
        $this->content = array_merge($this->content, $content);

        return $this;
    }

    public function make()
    {
        $this->group('link-fieldset', 'fieldset', function ($form) {
            $form->legend('link-legend', t('Add a link in the menu'))
                ->group('link-title-group', 'div', function ($form) {
                    $form->label('link-title-label', t('Link title'), [
                        'for' => 'title_link' ])
                    ->text('title_link', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => t('Example: Home'),
                        'required'    => 1,
                        'value'       => $this->content[ 'title_link' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('link-link-group', 'div', function ($form) {
                    $form->label('link-link-label', t('Link'))
                    ->text('link', [
                        'class'       => 'form-control',
                        'placeholder' => t('Example: node/1 or http://foo.com'),
                        'required'    => 1,
                        'value'       => $this->content[ 'link' ] . (!empty($this->content[ 'fragment' ])
                            ? '#' . $this->content[ 'fragment' ]
                            : '')
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('link-icon-group', 'div', function ($form) {
                    $form->label('link-icon-label', t('Icon'), [
                        'data-tooltip' => t('Icons are created from the CSS class of FontAwesome'),
                        'for'          => 'icon'
                    ])
                    ->group('link-icon-group', 'div', function ($form) {
                        $form->text('icon', [
                            'class'       => 'form-control text_icon',
                            'maxlength'   => 255,
                            'placeholder' => 'fa fa-bars, fa fa-home...',
                            'value'       => $this->content[ 'icon' ],
                        ])
                        ->html('btn-icon', '<button:css:attr>:_content</button>', [
                            '_content'     => '<i class="' . $this->content[ 'icon' ] . '" aria-hidden="true"></i>',
                            'aria-label'   => t('Rendering'),
                            'class'        => 'btn render_icon',
                            'type'         => 'button',
                            'data-tooltip' => t('Rendering')
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ])
                ->group('link-target-group', 'div', function ($form) {
                    $form->label('link-target-label', t('Target'))
                    ->select('target_link', self::getTarget(), [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $this->content[ 'target_link' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
        })
            ->token('token_link_form')
            ->html('cancel', '<button:css:attr>:_content</button>', [
                '_content' => t('Cancel'),
                'class'    => 'btn btn-danger',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ])
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return $this;
    }
    
    protected static function getTarget()
    {
        return [
            [ 'value' => '_blank', 'label' => '(_blank) ' . t('Load in a new window') ],
            [ 'value' => '_self', 'label' => '(_self) ' . t('Load in the same window') ],
            [ 'value' => '_parent', 'label' => '(_parent) ' . t('Load into the parent frameset') ],
            [ 'value' => '_top', 'label' => '(_top) ' . t('Load in the whole body of the window') ]
        ];
    }
}
