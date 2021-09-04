<?php

declare(strict_types=1);

namespace SoosyzeCore\FileManager\Form;

class FormFolder extends \Soosyze\Components\Form\FormBuilder
{
    /**
     * @var array
     */
    protected $values = [ 'name' => '' ];

    public function makeFields(): self
    {
        $this
            ->group('folder-fieldset', 'fieldset', function ($form) {
                $form->legend('folder-legend', t('Add folder'))
                ->group('name-group', 'div', function ($form) {
                    $form->label('name-label', t('Name'), [
                        'data-tooltip' => t('All non-alphanumeric characters or hyphens will be replaced by an underscore (_) or their unaccented equivalent.')
                    ])
                    ->text('name', [
                        'class'     => 'form-control',
                        'maxlenght' => 255,
                        'required'  => 1,
                        'value'     => $this->values[ 'name' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('submit-group', 'div', function ($form) {
                $form->token('token_folder')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
            });

        return $this;
    }
}
