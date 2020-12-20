<?php

namespace SoosyzeCore\Contact\Form;

class FormContact extends \Soosyze\Components\Form\FormBuilder
{
    protected $values = [
        'name'    => '',
        'email'   => '',
        'object'  => '',
        'message' => '',
    ];

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function setValues(array $values)
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    public function makeFields()
    {
        $this->name()
            ->email()
            ->object()
            ->message()
            ->copy()
            ->token('token_contact')
            ->submit('submit', t('Send the message'), [ 'class' => 'btn btn-success' ]);

        return $this;
    }

    protected function name()
    {
        return $this->group('name-group', 'div', function ($form) {
            $form->label('name-label', t('Name'))
                    ->text('name', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->values[ 'name' ]
                ]);
        }, self::$attrGrp);
    }

    protected function email()
    {
        return $this->group('email-group', 'div', function ($form) {
            $form->label('email-label', t('E-mail'))
                    ->email('email', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->values[ 'email' ]
                ]);
        }, self::$attrGrp);
    }

    protected function object()
    {
        return $this->group('object-group', 'div', function ($form) {
            $form->label('object-label', t('Object'))
                    ->text('object', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $this->values[ 'object' ]
                ]);
        }, self::$attrGrp);
    }

    protected function message()
    {
        return $this->group('message-group', 'div', function ($form) {
            $form->label('message-label', t('Message'))
                    ->textarea('message', $this->values[ 'message' ], [
                        'class'    => 'form-control',
                        'required' => 1,
                        'rows'     => 8
                ]);
        }, self::$attrGrp);
    }

    protected function copy()
    {
        return $this->group('copy-group', 'div', function ($form) {
            $form->checkbox('copy')
                    ->label('copy-label', '<i class="ui" aria-hidden="true"></i> ' . t('Send me a copy of the mail'), [
                        'for' => 'copy'
                ]);
        }, self::$attrGrp);
    }
}
