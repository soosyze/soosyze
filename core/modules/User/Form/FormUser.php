<?php

namespace SoosyzeCore\User\Form;

use Soosyze\Components\Form\FormBuilder;

class FormUser extends FormBuilder
{
    protected $content = [
        'username'  => '',
        'email'     => '',
        'picture'   => '',
        'bio'       => '',
        'name'      => '',
        'firstname' => '',
        'actived'   => '',
    ];

    protected $file;

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function __construct(array $attributes, $file = null, $config = null)
    {
        parent::__construct($attributes);
        $this->file   = $file;
        $this->config = $config;
    }

    public function content($content)
    {
        $this->content = array_merge($this->content, $content);

        return $this;
    }

    public function username(&$form)
    {
        $form->group('user-username-group', 'div', function ($form) {
            $form->label('user-username-label', t('User name'))
                ->text('username', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'required'  => 1,
                    'value'     => $this->content[ 'username' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function email(&$form)
    {
        $form->group('user-email-group', 'div', function ($form) {
            $form->label('user-email-label', t('E-mail'))
                ->email('email', [
                    'class'       => 'form-control',
                    'maxlength'   => 254,
                    'placeholder' => t('example@mail.com'),
                    'required'    => 1,
                    'value'       => $this->content[ 'email' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function picture(&$form)
    {
        $form->group('user-picture-group', 'div', function ($form) {
            $form->label('user-picture-label', t('Picture'), [
                'for'          => 'picture',
                'data-tooltip' => t('200ko maximum. Allowed extensions: jpeg, jpg, png.')
            ]);
            $this->file->inputFile('picture', $form, $this->content[ 'picture' ]);
        }, self::$attrGrp);

        return $this;
    }

    public function bio(&$form)
    {
        $form->group('system-description-group', 'div', function ($form) {
            $form->label('system-bio-label', t('Biography'), [
                    'data-tooltip' => t('Describe yourself in 255 characters maximum.')
                ])
                ->textarea('bio', $this->content[ 'bio' ], [
                    'class'       => 'form-control',
                    'maxlength'   => 255,
                    'placeholder' => t('Describe yourself in 255 characters maximum.'),
                    'rows'        => 3
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function name(&$form)
    {
        $form->group('user-name-group', 'div', function ($form) {
            $form->label('user-name-label', t('Name'))
                ->text('name', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $this->content[ 'name' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function firstname(&$form)
    {
        $form->group('user-firstname-group', 'div', function ($form) {
            $form->label('user-firstname-label', t('First name'))
                ->text('firstname', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $this->content[ 'firstname' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function passwordCurrent(&$form)
    {
        $this->password($form, 'password', t('Password'));

        return $this;
    }

    public function passwordNew( &$form )
    {
        $this->password($form, 'password_new', t('New Password'), $this->config->get('settings.password_show', true)
                ? [ 'onkeyup' => 'passwordPolicy(this)' ]
                : []
        );

        return $this;
    }

    public function passwordConfirm(&$form)
    {
        $this->password($form, 'password_confirm', t('Confirmation of the new password'));

        return $this;
    }

    public function password(&$form, $id, $label, array $attr = [])
    {
        $form->group("user-$id-group", 'div', function ($form) use ($id, $label, $attr) {
            $form->label("$id-label", $label, [ 'for' => $id ])
                ->group("user-$id-group", 'div', function ($form) use ($id, $attr) {
                    $form->password($id, [
                        'class'    => 'form-control'
                    ] + $attr);
                    if ($this->config && $this->config->get('settings.password_show', true)) {
                        $form->html("{$id}_show", '<button:css:attr>:_content</button>', [
                            'class'        => 'btn btn-toogle-password',
                            'onclick'      => "togglePassword(this, '$id')",
                            'type'         => 'button',
                            '_content'     => '<i class="fa fa-eye eyeIcon" aria-hidden="true"></i>',
                            'data-tooltip' => t('Show/Hide password'),
                            'aria-label'   => t('Show/Hide password')
                        ]);
                    }
                }, [ 'class' => 'form-group-flex' ]);
        }, self::$attrGrp);
    }

    public function fieldsetInformationsCreate()
    {
        return $this->group('user-informations-fieldset', 'fieldset', function ($form) {
            $form->legend('user-informations-legend', t('Information'));
            $this->username($form)
                    ->email($form);
        });
    }

    public function fieldsetInformations()
    {
        return $this->group('user-informations-fieldset', 'fieldset', function ($form) {
            $form->legend('user-informations-legend', t('Information'));
            $this->username($form)
                    ->email($form)
                    ->passwordCurrent($form);
        });
    }

    public function fieldsetProfil()
    {
        return $this->group('user-profil-fieldset', 'fieldset', function ($form) {
            $form->legend('user-informations-legend', t('Profile'));
            $this->picture($form)
                    ->bio($form)
                    ->name($form)
                    ->firstname($form);
        });
    }

    public function fieldsetPassword()
    {
        return $this->group('user-password-fieldset', 'fieldset', function ($form)
            {
                $form->legend('user-password-legend', t('Password'));
                $this->passwordNew($form)
                    ->passwordConfirm($form)
                    ->passwordPolicy($form);
            });
    }

    public function passwordPolicy(&$form)
    {
        if ($this->config && $this->config->get('settings.password_policy', true)) {
            if (($length = (int) $this->config->get('settings.password_length', 8)) < 8) {
                $length = 8;
            }
            if (($upper = (int) $this->config->get('settings.password_upper', 1)) < 1) {
                $upper = 1;
            }
            if (($digit = (int) $this->config->get('settings.password_digit', 1)) < 1) {
                $digit = 1;
            }
            if (($special = (int) $this->config->get('settings.password_special', 1)) < 1) {
                $special = 1;
            }

            $content = '<li data-pattern=".{' . $length . ',}">' . t('Minimum length') . " : $length</li>"
                . '<li data-pattern="(?=.*[A-Z]){' . $upper . ',}">' . t('Number of uppercase characters') . " : $upper</li>"
                . '<li data-pattern="(?=.*\d){' . $digit . ',}">' . t('Number of numeric characters') . " : $digit</li>"
                . '<li data-pattern="(?=.*\W){' . $special . ',}">' . t('Number of special characters') . " : $special</li>";
            $form->html('password_policy', '<ul:css:attr>:_content</ul>', [
                '_content' => $content,
            ]);
        }
    }

    public function fieldsetActived()
    {
        return $this->group('user-actived-fieldset', 'fieldset', function ($form) {
            $form->legend('user-actived-legend', t('Status'))
                    ->group('user-actived-fieldset', 'div', function ($form) {
                        $form->checkbox('actived', [ 'checked' => $this->content[ 'actived' ] ])
                        ->label('user-actived-label', '<span class="ui"></span> ' . t('Active'), [
                            'for' => 'actived' ]);
                    }, self::$attrGrp);
        });
    }

    public function fieldsetRoles($roles, $roles_user = [])
    {
        return $this->group('user-role-fieldset', 'fieldset', function ($form) use ($roles, $roles_user) {
            $form->legend('user-role-legend', t('User Roles'));
            foreach ($roles as $role) {
                $attrRole = [
                        'checked'  => in_array($role[ 'role_id' ], $roles_user),
                        'value'    => $role[ 'role_id' ],
                        'disabled' => $role[ 'role_id' ] <= 2
                    ];
                $form->group('user-role-' . $role[ 'role_id' ] . '-group', 'div', function ($form) use ($role, $attrRole) {
                    $form->checkbox('role[' . $role[ 'role_id' ] . ']', $attrRole)
                            ->label(
                                'user-role-' . $role[ 'role_id' ] . '-label',
                                '<span class="ui"></span>'
                                . '<span class="badge-role" style="background-color: ' . $role[ 'role_color' ] . '">'
                                . '<i class="' . $role[ 'role_icon' ] . '" aria-hidden="true"></i>'
                                . '</span> '
                                . t($role[ 'role_label' ]),
                                [ 'for' => 'role[' . $role[ 'role_id' ] . ']' ]
                        );
                }, self::$attrGrp);
            }
        });
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function submitForm($label = 'Save')
    {
        return $this->token('token_user_form')
                ->submit('sumbit', t($label), [ 'class' => 'btn btn-success' ]);
    }
}
