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
                ->token()
                ->submit('submit', 'Envoyer le message', [ 'class' => 'btn btn-success' ]);
    }

    protected function name()
    {
        return $this->group('contact-name-group', 'div', function ($form) {
            $form->label('contact-name-label', 'Votre nom')
                    ->text('name', 'name', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->content[ 'name' ]
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function email()
    {
        return $this->group('contact-email-group', 'div', function ($form) {
            $form->label('contact-email-label', 'Votre adresse de courriel')
                    ->email('email', 'email', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->content[ 'email' ]
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function object()
    {
        return $this->group('contact-object-group', 'div', function ($form) {
            $form->label('contact-object-label', 'Objet')
                    ->text('object', 'object', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->content[ 'object' ]
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function message()
    {
        return $this->group('contact-message-group', 'div', function ($form) {
            $form->label('contact-message-label', 'Message')
                    ->textarea('message', 'message', $this->content[ 'message' ], [
                        'class'    => 'form-control',
                        'required' => 1,
                        'rows'     => 8,
                        'style'    => 'resize:vertical'
                ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function copy()
    {
        return $this->group('contact-copy-group', 'div', function ($form) {
            $form->checkbox('copy', 'copy')
                    ->label('contact-copy-label', 'M\'envoyer une copie du mail', [
                        'for' => 'copy'
                    ]);
        }, [ 'class' => 'form-group' ]);
    }
}
