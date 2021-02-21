<?php

namespace SoosyzeCore\System\Hook;

class ConfigMailer implements \SoosyzeCore\Config\ConfigInterface
{
    private static $attrGrp = [ 'class' => 'form-group' ];

    public function defaultValues()
    {
        return [
            'email'           => '',
            'driver'          => '',
            'smtp_host'       => '',
            'smtp_port'       => '',
            'smtp_encryption' => '',
            'smtp_username'   => '',
            'smtp_password'   => ''
        ];
    }

    public function menu(array &$menu)
    {
        $menu[ 'mailer' ] = [
            'title_link' => 'Email',
            'config'     => 'mailer'
        ];
    }

    public function form(&$form, array $data, $req)
    {
        $form->group('information-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('information-legend', t('Information'))
                ->group('email-group', 'div', function ($form) use ($data) {
                    $form->label('email-label', t('E-mail of the site'), [
                        'data-tooltip' => t('E-mail used for the general configuration, for your contacts, the recovery of your password ...')
                    ])
                    ->email('email', [
                        'class'       => 'form-control',
                        'required'    => 1,
                        'placeholder' => t('E-mail'),
                        'value'       => $data[ 'email' ]
                    ]);
                }, self::$attrGrp);
        })
            ->group('driver-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('driver-legend', t('Sélectionner un driver'))
                ->group('driver-group', 'div', function ($form) use ($data) {
                    $form->label('driver-label', t('Driver'))
                    ->select('driver', [
                        [ 'value' => 'mail', 'label' => 'mail' ],
                        [ 'value' => 'smtp', 'label' => 'smtp' ]
                        ], [
                        'class'       => 'form-control',
                        'data-toogle' => 'select',
                        'required'    => 1,
                        ':selected'   => $data[ 'driver' ]
                    ]);
                }, self::$attrGrp);
            })
            ->group('smtp-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('smtp-legend', t('SMTP Configuration'))
                ->group('smtp_host-group', 'div', function ($form) use ($data) {
                    $form->label('smtp_host-label', t('Host Address'))
                    ->text('smtp_host', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'smtp1.example.com',
                        'value'       => $data[ 'smtp_host' ]
                    ]);
                }, self::$attrGrp)
                ->group('smtp_port-group', 'div', function ($form) use ($data) {
                    $form->label('smtp_port-label', t('Host Port'))
                    ->text('smtp_port', [
                        'class'       => 'form-control',
                        'maxlength'   => 5,
                        'placeholder' => 465,
                        'value'       => $data[ 'smtp_port' ]
                    ]);
                }, self::$attrGrp)
                ->group('smtp_encryption-group', 'div', function ($form) use ($data) {
                    $form->label('smtp_encryption-label', t('Encryption Protocol'))
                    ->select('smtp_encryption', [
                        [ 'value' => 'none', 'label' => 'none' ],
                        [ 'value' => 'ssl', 'label' => 'SSL (Secure Sockets Layer)' ],
                        [ 'value' => 'tls', 'label' => 'TLS (Transport Layer Security)' ]
                        ], [
                        'class'     => 'form-control',
                        ':selected' => $data[ 'smtp_encryption' ]
                    ]);
                }, self::$attrGrp)
                ->group('smtp_username-group', 'div', function ($form) use ($data) {
                    $form->label('smtp_username-label', t('Serveur Username'))
                    ->text('smtp_username', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => 'user@example.com',
                        'value'       => $data[ 'smtp_username' ]
                    ]);
                }, self::$attrGrp)
                ->group('smtp_password-group', 'div', function ($form) use ($data) {
                    $form->label('smtp_password-label', t('Server Password'))
                    ->password('smtp_password', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'value'     => $data[ 'smtp_password' ]
                    ]);
                }, self::$attrGrp);
            }, [ 'class' => 'select-pane' . ($data[ 'driver' ] === 'smtp' ? ' active' : ''), 'id' => 'smtp' ]);
    }

    public function validator(&$validator)
    {
        $rules  = [
            'email'  => 'required|email|max:254',
            'driver' => 'required|inarray:mail,smtp'
        ];
        $labels = [
            'email'  => t('E-mail of the site'),
            'driver' => t('Driver')
        ];

        if ($validator->getInput('driver') === 'smtp') {
            $rules  += [
                'smtp_host'       => 'required|url',
                'smtp_port'       => 'required|numeric|between_numeric:0,65535',
                'smtp_encryption' => 'required|inarray:tls,ssl',
                'smtp_username'   => 'required|email',
                'smtp_password'   => 'required|string'
            ];
            $labels += [
                'smtp_host'       => t('Host Address'),
                'smtp_port'       => t('Host Port'),
                'smtp_encryption' => t('Encryption Protocol'),
                'smtp_username'   => t('Serveur Username'),
                'smtp_password'   => t('Server Password')
            ];
        }

        $validator->setRules($rules)
            ->setLabels($labels);
    }

    public function before(&$validator, array &$data, $id)
    {
        $data = [
            'email'           => $validator->getInput('email'),
            'driver'          => $validator->getInput('driver'),
            'smtp_host'       => $validator->getInput('smtp_host'),
            'smtp_port'       => (int) $validator->getInput('smtp_port'),
            'smtp_encryption' => $validator->getInput('smtp_encryption'),
            'smtp_username'   => $validator->getInput('smtp_username'),
            'smtp_password'   => $validator->getInput('smtp_password')
        ];
    }

    public function files(array &$inputFiles)
    {
    }

    public function after(&$validator, array $data, $id)
    {
    }
}
