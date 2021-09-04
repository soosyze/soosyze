<?php

declare(strict_types=1);

namespace SoosyzeCore\Block\Form;

class FormDeleteBlock extends \Soosyze\Components\Form\FormBuilder
{
    public function makeFields(): self
    {
        $this->group('file-fieldset', 'fieldset', function ($form) {
            $form->legend('file-legend', t('Delete block'))
                ->group('info-group', 'div', function ($form) {
                    $form->html('info', '<p:attr>:content</p>', [
                        ':content' => t('Warning ! The deletion of the block is final.')
                    ]);
                }, [ 'class' => 'alert alert-warning' ]);
        })
            ->group('submit-group', 'div', function ($form) {
                $form->token('token_block_delete')
                ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ]);
            });

        return $this;
    }
}
