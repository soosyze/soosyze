<?php

namespace SoosyzeCore\Node\Form;

class FormNodeDelete extends \Soosyze\Components\Form\FormBuilder
{
    protected $values = [
        'path'  => '',
        'files' => 1
    ];

    protected $useInPath;

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function setValues(array $values, $useInPath)
    {
        $this->values    = array_merge($this->values, $values);
        $this->useInPath = $useInPath;

        return $this;
    }

    public function makeFields()
    {
        return $this->group('info-fieldset', 'fieldset', function ($form) {
            $form->legend('info-legend', t('Node deletion'))
                    ->group('info-group', 'div', function ($form) {
                        $form->html('info', '<p:attr>:_content</p>', [
                            '_content' => t('Warning ! The deletion of the node is final.')
                        ]);

                        if ($this->useInPath) {
                            $form->html('info_path', '<p:attr>:_content</p>', [
                                '_content' => t('This content is used in the configuration as') . ' : <b>' . t($this->useInPath[ 'title' ]) . '</b>'
                            ]);
                        }
                    }, [ 'class' => 'alert alert-warning' ]);

            if ($this->useInPath) {
                $form->group('path-group', 'div', function ($form) {
                    $form
                            ->label('path-label', t('New path for') . ' ' . t($this->useInPath[ 'title' ]))
                            ->text('path', [
                                'class'       => 'form-control',
                                'maxlength'   => 512,
                                'placeholder' => t('Example: node/1'),
                                'required'    => !empty($this->useInPath[ 'required' ]),
                                'value'       => $this->values[ 'path' ]
                            ]);
                }, [ 'class' => 'form-group' ]);
            }

            $form->group('files-group', 'div', function ($form) {
                $form->checkbox('files', [
                            'checked' => $this->values[ 'files' ],
                            'id'      => 'files'
                        ])
                        ->label(
                            'files-label',
                            '<span class="ui"></span> ' . t('Delete files with their contents'),
                            [ 'for' => 'files' ]
                        );
            }, [ 'class' => 'form-group' ]);
        })
        ->token('token_node_remove')
        ->submit('sumbit', t('Delete'), [ 'class' => 'btn btn-danger' ]);
    }
}
