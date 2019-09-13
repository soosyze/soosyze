<?php

namespace SoosyzeCore\Contact\Form;

use Soosyze\Components\Form\FormBuilder;

class FormContact extends FormBuilder
{
    protected $content = [];

    public function generate($content)
    {
        $this->content = $content;

        return $this->name()
                ->email()
                ->object()
                ->message()
                ->copy()
                ->token('token_contact')
                ->submit('submit', t('Send the message'), [ 'class' => 'btn btn-success' ]);
    }

    protected function name()
    {
        return $this->group('contact-name-group', 'div', function ($form) {
            $form->label('contact-name-label', t('Name'))
                    ->text('name', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->content[ 'name' ]
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function email()
    {
        return $this->group('contact-email-group', 'div', function ($form) {
            $form->label('contact-email-label', t('E-mail'))
                    ->email('email', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->content[ 'email' ]
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function object()
    {
        return $this->group('contact-object-group', 'div', function ($form) {
            $form->label('contact-object-label', t('Object'))
                    ->text('object', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->content[ 'object' ]
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function message()
    {
        return $this->group('contact-message-group', 'div', function ($form) {
            $form->label('contact-message-label', t('Message'))
                    ->textarea('message', $this->content[ 'message' ], [
                        'class'    => 'form-control',
                        'required' => 1,
                        'rows'     => 8
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function copy()
    {
        return $this->group('contact-copy-group', 'div', function ($form) {
            $form->checkbox('copy')
                    ->label('contact-copy-label', '<i class="ui" aria-hidden="true"></i> ' . t('Send me a copy of the mail'), [
                        'for' => 'copy'
                    ]);
        }, [ 'class' => 'form-group' ]);
    }
}
