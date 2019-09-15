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
        $menu['user'] = [
            'title_link' => 'User'
        ];
    }

    public function form(&$form, $data)
    {
        return $form
                ->group('config-login-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('config-login-legend', t('Log in'))
                    ->group('config-relogin-group', 'div', function ($form) use ($data) {
                        $form->label('config-connect_url-label', t('Protection of connection paths'), [
                            'data-tooltip' => t('If the site is managed by a restricted team, you can choose a suffix for the URL to better protect your login form.')
                            . t('Example: Ab1P-9eM_s8Y = user / login / Ab1P-9eM_s8Y')
                        ])
                        ->text('connect_url', [
                            'class'       => 'form-control',
                            'minlength'   => 10,
                            'placeholder' => t('Add a token to your connection routes (10 characters minimum)'),
                            'value'       => $data[ 'connect_url' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-register-group', 'div', function ($form) use ($data) {
                        $form->label('config-connect_redirect-label', t('Redirect page after connection'))
                        ->text('connect_redirect', [
                            'class'       => 'form-control',
                            'maxlength'   => 255,
                            'placeholder' => '',
                            'value'       => $data[ 'connect_redirect' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('config-inscription-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('config-inscription-legend', t('Registration'))
                    ->group('config-register-group', 'div', function ($form) use ($data) {
                        $form->checkbox('user_register', [ 'checked' => $data[ 'user_register' ] ])
                        ->label('config-register-label', '<span class="ui"></span> ' . t('Open registration'), [
                            'for' => 'user_register'
                        ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('config-password-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('config-password-legend', t('Password policy'))
                    ->group('config-relogin-group', 'div', function ($form) use ($data) {
                        $form->checkbox('user_relogin', [ 'checked' => $data[ 'user_relogin' ] ])
                        ->label('config-relogin-label', '<span class="ui"></span> ' . t('Open password recovery'), [
                            'for' => 'user_relogin'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_show-group', 'div', function ($form) use ($data) {
                        $form->checkbox('password_show', [ 'checked' => $data[ 'password_show' ] ])
                        ->label('config-password_show-label', '<span class="ui"></span> ' . t('Add a button to view passwords'), [
                            'for' => 'password_show'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_length-group', 'div', function ($form) use ($data) {
                        $form->label('config-password_length-label', t('Minimum length'))
                        ->number('password_length', [
                            'class' => 'form-control',
                            'min'   => 8,
                            'value' => $data[ 'password_length' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_upper-group', 'div', function ($form) use ($data) {
                        $form->label('config-upper-label', t('Number of uppercase characters'))
                        ->number('password_upper', [
                            'class' => 'form-control',
                            'min'   => 1,
                            'value' => $data[ 'password_upper' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_digit-group', 'div', function ($form) use ($data) {
                        $form->label('config-password_digit-label', t('Number of numeric characters'))
                        ->number('password_digit', [
                            'class' => 'form-control',
                            'min'   => 1,
                            'value' => $data[ 'password_digit' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-password_special-group', 'div', function ($form) use ($data) {
                        $form->label('config-password_special-label', t('Number of special characters'))
                        ->number('password_special', [
                            'class' => 'form-control',
                            'min'   => 1,
                            'value' => $data[ 'password_special' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->token('token_user_config')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'user_register'    => 'bool',
            'user_relogin'     => 'bool',
            'connect_url'      => '!required|string|min:10|slug',
            'connect_redirect' => '!required|string|max:255',
            'password_show'    => 'bool',
            'password_length'  => 'int|min:8',
            'password_upper'   => 'int|min:1',
            'password_digit'   => 'int|min:1'
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'user_register'    => $validator->getInput('user_register'),
            'user_relogin'     => $validator->getInput('user_relogin'),
            'connect_url'      => $validator->getInput('connect_url'),
            'connect_redirect' => $validator->getInput('connect_redirect'),
            'password_show'    => $validator->getInput('password_show'),
            'password_length'  => $validator->getInput('password_length'),
            'password_upper'   => $validator->getInput('password_upper'),
            'password_digit'   => $validator->getInput('password_digit')
        ];
    }
}
