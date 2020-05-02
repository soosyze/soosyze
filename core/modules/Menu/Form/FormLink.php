<?php

namespace SoosyzeCore\Menu\Form;

class FormLink extends \Soosyze\Components\Form\FormBuilder
{
    protected $isRewrite = false;

    protected $values = [
        'title_link'  => '',
        'link'        => '',
        'query'       => '',
        'fragment'    => '',
        'icon'        => '',
        'target_link' => '_self'
    ];

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function setValues($value)
    {
        $this->values = array_merge($this->values, $value);

        return $this;
    }
    
    public function setRewrite($isRewrite)
    {
        $this->isRewrite = $isRewrite ? '?' : '&';

        return $this;
    }

    public function makeFields()
    {
        $this->group('link-fieldset', 'fieldset', function ($form) {
            $form->legend('link-legend', t('Add a link in the menu'))
                ->group('title_link-group', 'div', function ($form) {
                    $form->label('title_link-label', t('Link title'))
                    ->text('title_link', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => t('Example: Home'),
                        'required'    => 1,
                        'value'       => $this->values[ 'title_link' ]
                    ]);
                }, self::$attrGrp)
                ->group('link-group', 'div', function ($form) {
                    $form->label('link-label', t('Link'))
                    ->text('link', [
                        'class'       => 'form-control',
                        'placeholder' => t('Example: node/1 or http://foo.com'),
                        'required'    => 1,
                        'value'       => $this->values[ 'link' ] .
                        (!empty($this->values[ 'query' ])
                            ? $this->isRewrite . $this->values[ 'query' ]
                            : '') . (!empty($this->values[ 'fragment' ])
                            ? '#' . $this->values[ 'fragment' ]
                            : '')
                    ]);
                }, self::$attrGrp)
                ->group('icon-group', 'div', function ($form) {
                    $form->label('icon-label', t('Icon'), [
                        'data-tooltip' => t('Icons are created from the CSS class of FontAwesome'),
                        'for'          => 'icon'
                    ])
                    ->group('icon-flex', 'div', function ($form) {
                        $form->text('icon', [
                            'class'       => 'form-control text_icon',
                            'maxlength'   => 255,
                            'placeholder' => 'fa fa-bars, fa fa-home...',
                            'value'       => $this->values[ 'icon' ],
                        ])
                        ->html('icon-btn', '<button:attr>:_content</button>', [
                            '_content'     => '<i class="' . $this->values[ 'icon' ] . '" aria-hidden="true"></i>',
                            'aria-label'   => t('Rendering'),
                            'class'        => 'btn render_icon',
                            'type'         => 'button',
                            'data-tooltip' => t('Rendering')
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, self::$attrGrp)
                ->group('target_link-group', 'div', function ($form) {
                    $form->label('target_link-label', t('Target'))
                        ->group('target_link_1-group', 'div', function ($form) {
                            $form->radio('target_link', [
                                'id'       => 'target_link_1',
                                'value'    => '_self',
                                'required' => 1,
                                'checked' => ($this->values[ 'target_link' ] === '_self')
                            ])->label('target_link-label', '(_self) ' . t('Load in the same window'), [
                                'for' => 'target_link_1'
                            ]);
                        }, self::$attrGrp)
                        ->group('target_link_2-group', 'div', function ($form) {
                            $form->radio('target_link', [
                                'id'       => 'target_link_2',
                                'value'    => '_blank',
                                'required' => 1,
                                'checked' => ($this->values[ 'target_link' ] === '_blank')
                            ])->label('target_link-label', '(_blank) ' . t('Load in a new window'), [
                                'for' => 'target_link_2'
                            ]);
                        }, self::$attrGrp);
                }, self::$attrGrp);
        })
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
