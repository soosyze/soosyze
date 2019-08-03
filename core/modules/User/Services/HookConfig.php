<?php

namespace SoosyzeCore\User\Services;

class HookConfig
{
    /**
     * @var \Soosyze\Config
     */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function menu(&$menu)
    {
        $menu[] = [
            'key'        => 'user',
            'title_link' => 'Utilisateur'
        ];
    }

    public function form(&$form, $data)
    {
        return $form->group('config-inscription-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('config-inscription-legend', 'Inscription')
                    ->group('config-register-group', 'div', function ($form) use ($data) {
                        $form->checkbox('user_register', [ 'checked' => $data[ 'user_register' ] ])
                        ->label('config-register-label', '<span class="ui"></span> Ouvrir l\'inscription', [
                            'for' => 'user_register'
                        ]);
                    }, [ 'class' => 'form-group' ]);
        })->group('config-password-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('config-password-legend', 'Politique des mots de passe')
                    ->group('config-relogin-group', 'div', function ($form) use ($data) {
                        $form->checkbox('user_relogin', [ 'checked' => $data[ 'user_relogin' ] ])
                        ->label('config-relogin-label', '<span class="ui"></span> Ouvrir la récupération de mot de passe', [
                            'for' => 'user_relogin'
                        ]);
                    }, [ 'class' => 'form-group' ])
                        ->group('config-password_show-group', 'div', function ($form) use ($data) {
                            $form->checkbox('password_show', [ 'checked' => $data[ 'password_show' ] ])
                        ->label('config-password_show-label', '<span class="ui"></span> Ajout d\'un bouton <i class="fa fa-eye" aria-hidden="true"></i> pour visualiser les mots de passe', [
                            'for' => 'password_show'
                        ]);
                        }, [ 'class' => 'form-group' ])
                    ->group('config-password_length-group', 'div', function ($form) use ($data) {
                        $form->label('config-password_length-label', 'Longueur minimum')
                        ->number('password_length', [
                            'class' => 'form-control',
                            'min'   => 8,
                            'value' => $data[ 'password_length' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_upper-group', 'div', function ($form) use ($data) {
                        $form->label('config-upper-label', 'Nombre de caractères majuscule')
                        ->number('password_upper', [
                            'class' => 'form-control',
                            'min'   => 1,
                            'value' => $data[ 'password_upper' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_digit-group', 'div', function ($form) use ($data) {
                        $form->label('config-password_digit-label', 'Nombre de caractères numérique')
                        ->number('password_digit', [
                            'class' => 'form-control',
                            'min'   => 1,
                            'value' => $data[ 'password_digit' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_special-group', 'div', function ($form) use ($data) {
                        $form->label('config-password_special-label', 'Nombre de caractères spéciaux')
                        ->number('password_special', [
                            'class' => 'form-control',
                            'min'   => 1,
                            'value' => $data[ 'password_special' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
        })
                ->token('token_user_config')
                ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'user_register'   => 'bool',
            'user_relogin'    => 'bool',
            'password_show'   => 'bool',
            'password_length' => 'int|min:8',
            'password_upper'  => 'int|min:1',
            'password_digit'  => 'int|min:1'
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'user_register'   => $validator->getInput('user_register'),
            'user_relogin'    => $validator->getInput('user_relogin'),
            'password_show'   => $validator->getInput('password_show'),
            'password_length' => $validator->getInput('password_length'),
            'password_upper'  => $validator->getInput('password_upper'),
            'password_digit'  => $validator->getInput('password_digit')
        ];
    }
}
