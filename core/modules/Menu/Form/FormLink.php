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
        'target_link' => false
    ];

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function setValues($value)
    {
        $this->values = array_merge($this->values, $value);

        return $this;
    }

    public function setRewrite($isRewrite)
    {
        $this->isRewrite = $isRewrite
            ? '?'
            : '&';

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
                    $form->checkbox('target_link', [
                        'id'      => 'target_link',
                        'checked' => $this->values[ 'target_link' ]
                    ])
                    ->label('target_link-label', '<span class="ui"></span>' . t('Open in a new window'), [
                        'for' => 'target_link'
                    ]);
                }, self::$attrGrp);
        })
            ->token('token_link_form')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ])
            ->html('cancel', '<button:attr>:_content</button>', [
                '_content' => t('Cancel'),
                'class'    => 'btn btn-danger',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ]);

        return $this;
    }
}
