<?php

namespace SoosyzeCore\User\Services;

class HookConfig implements \SoosyzeCore\Config\Services\ConfigInterface
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function menu(&$menu)
    {
        $menu[ 'user' ] = [
            'title_link' => 'User'
        ];
    }

    public function form(&$form, $data, $req)
    {
        $form
            ->group('login-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('login-legend', t('Log in'))
                ->group('connect_url-group', 'div', function ($form) use ($data) {
                    $form->label('connect_url-label', t('Protection of connection paths'), [
                        'data-tooltip' => t('If the site is managed by a restricted team, you can choose a suffix for the URL to better protect your login form.')
                        . t('Example: Ab1P-9eM_s8Y : ')
                        . $this->router->getRoute('user.login', [ ':url' => '/Ab1P-9eM_s8Y' ]),
                        'for'          => 'connect_url'
                    ])
                    ->group('connect_url-flex', 'div', function ($form) use ($data) {
                        $form->html('base_path', '<span:attr>:_content</span>', [
                            '_content' => $this->router->getRoute('user.login', [
                                ':url' => '' ]),
                            'id'       => ''
                        ])
                        ->text('connect_url', [
                            'class'       => 'form-control',
                            'minlength'   => 10,
                            'placeholder' => t('Add a token to your connection routes (10 characters minimum)'),
                            'value'       => $data[ 'connect_url' ]
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ])
                ->group('connect_redirect-group', 'div', function ($form) use ($data) {
                    $form->label('connect_redirect-label', t('Redirect page after connection'), [
                        'for' => 'connect_redirect'
                    ])
                    ->group('connect_redirect-flex', 'div', function ($form) use ($data) {
                        $form->html('base_path', '<span:attr>:_content</span>', [
                            '_content' => $this->router->makeRoute(''),
                            'id'       => ''
                        ])
                        ->text('connect_redirect', [
                            'class'       => 'form-control',
                            'maxlength'   => 255,
                            'placeholder' => '',
                            'required'    => 1,
                            'value'       => $data[ 'connect_redirect' ]
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('user_register-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('user_register-legend', t('Registration'))
                ->group('user_register-group', 'div', function ($form) use ($data) {
                    $form->checkbox('user_register', [ 'checked' => $data[ 'user_register' ] ])
                    ->label('user_register-label', '<span class="ui"></span> ' . t('Open registration'), [
                        'for' => 'user_register'
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('eula-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('eula-legend', t('CGU et RGPD'))
                ->group('terms_of_service_show-group', 'div', function ($form) use ($data) {
                    $form->checkbox('terms_of_service_show', [ 'checked' => $data[ 'terms_of_service_show' ] ])
                    ->label('terms_of_service_show-label', '<span class="ui"></span> ' . t('Activate the Terms'), [
                        'for' => 'terms_of_service_show'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('terms_of_service_page-group', 'div', function ($form) use ($data) {
                    $form->label('terms_of_service_page-label', t('CGU page'), [
                        'data-tooltip' => t('Conditions générales d\'utilisation (CGU)')
                    ])
                    ->group('terms_of_service_page-flex', 'div', function ($form) use ($data) {
                        $form->html('base_path', '<span:attr>:_content</span>', [
                            '_content' => $this->router->makeRoute(''),
                            'id'       => ''
                        ])
                        ->text('terms_of_service_page', [
                            'class'       => 'form-control',
                            'maxlength'   => 255,
                            'placeholder' => 'Exemple : node/1',
                            'value'       => $data[ 'terms_of_service_page' ]
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ])
                /* RGPD */
                ->group('rgpd_show-group', 'div', function ($form) use ($data) {
                    $form->checkbox('rgpd_show', [ 'checked' => $data[ 'rgpd_show' ] ])
                    ->label('rgpd_show-label', '<span class="ui"></span> ' . t('Enable Data Privacy Policy'), [
                        'for' => 'rgpd_show'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('rgpd_page-group', 'div', function ($form) use ($data) {
                    $form->label('rgpd_page-label', t('RGPD Page'), [
                        'data-tooltip' => t('Règlement général sur la protection des données (RGPD)')
                    ])
                    ->group('rgpd_page-flex', 'div', function ($form) use ($data) {
                        $form->html('base_path', '<span:attr>:_content</span>', [
                            '_content' => $this->router->makeRoute(''),
                            'id'       => ''
                        ])
                        ->text('rgpd_page', [
                            'class'       => 'form-control',
                            'maxlength'   => 255,
                            'placeholder' => 'Exemple : node/1',
                            'value'       => $data[ 'rgpd_page' ]
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('password-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('password-legend', t('Password policy'))
                ->group('user_relogin-group', 'div', function ($form) use ($data) {
                    $form->checkbox('user_relogin', [ 'checked' => $data[ 'user_relogin' ] ])
                    ->label('relogin-label', '<span class="ui"></span> ' . t('Open password recovery'), [
                        'for' => 'user_relogin'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password_show-group', 'div', function ($form) use ($data) {
                    $form->checkbox('password_show', [ 'checked' => $data[ 'password_show' ] ])
                    ->label('password_show-label', '<span class="ui"></span> ' . t('Add a button to view passwords'), [
                        'for' => 'password_show'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password_policy-group', 'div', function ($form) use ($data) {
                    $form->checkbox('password_policy', [ 'checked' => $data[ 'password_policy' ] ])
                    ->label('password_policy-label', '<span class="ui"></span> ' . t('Add visualization of the password policy'), [
                        'for' => 'password_policy'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password_length-group', 'div', function ($form) use ($data) {
                    $form->label('password_length-label', t('Minimum length'))
                    ->number('password_length', [
                        'class'    => 'form-control',
                        'min'      => 8,
                        'required' => 1,
                        'value'    => $data[ 'password_length' ] > 8
                            ? $data[ 'password_length' ]
                            : 8
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password_upper-group', 'div', function ($form) use ($data) {
                    $form->label('password_upper-label', t('Number of uppercase characters'))
                    ->number('password_upper', [
                        'class'    => 'form-control',
                        'min'      => 1,
                        'required' => 1,
                        'value'    => $data[ 'password_upper' ] > 1
                            ? $data[ 'password_upper' ]
                            : 1
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password_digit-group', 'div', function ($form) use ($data) {
                    $form->label('password_digit-label', t('Number of numeric characters'))
                    ->number('password_digit', [
                        'class'    => 'form-control',
                        'min'      => 1,
                        'required' => 1,
                        'value'    => $data[ 'password_digit' ] > 1
                            ? $data[ 'password_digit' ]
                            : 1
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('password_special-group', 'div', function ($form) use ($data) {
                    $form->label('password_special-label', t('Number of special characters'))
                    ->number('password_special', [
                        'class'    => 'form-control',
                        'min'      => 1,
                        'required' => 1,
                        'value'    => $data[ 'password_special' ] > 1
                            ? $data[ 'password_special' ]
                            : 1
                    ]);
                }, [ 'class' => 'form-group' ]);
            });
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'user_register'         => 'bool',
            'user_relogin'          => 'bool',
            'terms_of_service_show' => 'bool',
            'terms_of_service_page' => 'required_with:terms_of_service_show|route',
            'rgpd_show'             => 'bool',
            'rgpd_page'             => 'required_with:rgpd_show|route',
            'connect_url'           => '!required|string|min:10|regex:/^[\d\w\-]{10,}$/',
            'connect_redirect'      => 'required|route',
            'password_show'         => 'bool',
            'password_policy'       => 'bool',
            'password_length'       => 'min_numeric:8',
            'password_upper'        => 'min_numeric:1',
            'password_digit'        => 'min_numeric:1',
            'password_special'      => 'min_numeric:1'
        ])->setLabel([
            'user_register'         => t('Registration'),
            'user_relogin'          => t('Open password recovery'),
            'terms_of_service_show' => t('Activate the Terms'),
            'terms_of_service_page' => t('CGU page'),
            'rgpd_show'             => t('Enable Data Privacy Policy'),
            'rgpd_page'             => t('RGPD Page'),
            'connect_url'           => t('Protection of connection paths'),
            'connect_redirect'      => t('Redirect page after connection'),
            'password_show'         => t('Add a button to view passwords'),
            'password_policy'       => t('Add visualization of the password policy'),
            'password_length'       => t('Minimum length'),
            'password_upper'        => t('Number of uppercase characters'),
            'password_digit'        => t('Number of numeric characters'),
            'password_special'      => t('Number of special characters'),
        ]);
    }

    public function before(&$validator, &$data, $id)
    {
        $data = [
            'user_register'         => (bool) $validator->getInput('user_register'),
            'user_relogin'          => (bool) $validator->getInput('user_relogin'),
            'terms_of_service_show' => (bool) $validator->getInput('terms_of_service_show'),
            'terms_of_service_page' => $validator->getInput('terms_of_service_page'),
            'rgpd_show'             => (bool) $validator->getInput('rgpd_show'),
            'rgpd_page'             => $validator->getInput('rgpd_page'),
            'connect_url'           => $validator->getInput('connect_url'),
            'connect_redirect'      => $validator->getInput('connect_redirect'),
            'password_show'         => (bool) $validator->getInput('password_show'),
            'password_policy'       => (bool) $validator->getInput('password_policy'),
            'password_length'       => (int) $validator->getInput('password_length'),
            'password_upper'        => (int) $validator->getInput('password_upper'),
            'password_digit'        => (int) $validator->getInput('password_digit'),
            'password_special'      => (int) $validator->getInput('password_special')
        ];
    }

    public function after(&$validator, $data, $id)
    {
    }

    public function files(&$inputsFile)
    {
    }
}
