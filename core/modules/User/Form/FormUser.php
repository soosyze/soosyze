<?php

namespace SoosyzeCore\User\Form;

class FormUser extends \Soosyze\Components\Form\FormBuilder
{
    private static $attrGrp = [ 'class' => 'form-group' ];

    private $values = [
        'username'         => '',
        'email'            => '',
        'picture'          => '',
        'bio'              => '',
        'name'             => '',
        'firstname'        => '',
        'actived'          => '',
        'rgpd'             => '',
        'terms_of_service' => '',
        'roles'            => []
    ];

    private $file;

    public function __construct(array $attr, $file = null, $config = null)
    {
        parent::__construct($attr);
        $this->file   = $file;
        $this->config = $config;
    }

    public function setValues(array $values)
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    public function usernameGroup(&$form)
    {
        $form->group('username-group', 'div', function ($form) {
            $form->label('username-label', t('User name'))
                ->text('username', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'required'  => 1,
                    'value'     => $this->values[ 'username' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function emailGroup(&$form)
    {
        $form->group('email-group', 'div', function ($form) {
            $form->label('email-label', t('E-mail'))
                ->emailGroup('email', [
                    'class'       => 'form-control',
                    'maxlength'   => 254,
                    'placeholder' => t('example@mail.com'),
                    'required'    => 1,
                    'value'       => $this->values[ 'email' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function pictureGroup(&$form)
    {
        $form->group('picture-group', 'div', function ($form) {
            $form->label('picture-label', t('Picture'), [
                'for'          => 'picture',
                'data-tooltip' => t('200ko maximum. Allowed extensions: jpeg, jpg, png.')
            ]);
            $this->file->inputFile('picture', $form, $this->values[ 'picture' ]);
        }, self::$attrGrp);

        return $this;
    }

    public function bioGroup(&$form)
    {
        $form->group('bio-group', 'div', function ($form) {
            $form->label('bio-label', t('Biography'), [
                    'data-tooltip' => t('Describe yourself in 255 characters maximum.')
                ])
                ->textarea('bio', $this->values[ 'bio' ], [
                    'class'       => 'form-control',
                    'maxlength'   => 255,
                    'placeholder' => t('Describe yourself in 255 characters maximum.'),
                    'rows'        => 3
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function nameGroup(&$form)
    {
        $form->group('name-group', 'div', function ($form) {
            $form->label('name-label', t('Last name'))
                ->text('name', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $this->values[ 'name' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function firstnameGroup(&$form)
    {
        $form->group('firstname-group', 'div', function ($form) {
            $form->label('firstname-label', t('First name'))
                ->text('firstname', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $this->values[ 'firstname' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function eulaGroup(&$form, $router)
    {
        if (!$this->config) {
            return $this;
        }
        if ($this->config->get('settings.terms_of_service_show', false)) {
            $form->group('terms_of_service-group', 'div', function ($form) {
                $form->checkbox('terms_of_service', [ 'checked' => $this->values[ 'terms_of_service' ] ])
                    ->label('terms_of_service-label', '<span class="ui"></span> ' . t('I have read and accept your terms of service (Required)'), [
                        'for' => 'terms_of_service'
                    ]);
            }, self::$attrGrp)
                ->html('terms_of_service-info', '<p><a :attr>:content</a></p>', [
                    ':content' => t('Read the terms of service'),
                    'href'     => $router->makeRoute($this->config->get('settings.terms_of_service_page')),
                    'target'   => '_blank'
            ]);
        }
        if ($this->config->get('settings.rgpd_show', false)) {
            $form->group('rgpd-group', 'div', function ($form) {
                $form->checkbox('rgpd', [ 'checked' => $this->values[ 'rgpd' ] ])
                    ->label('rgpd-label', '<span class="ui"></span> ' . t('I have read and accept your privacy policy (Required)'), [
                        'for' => 'rgpd'
                    ]);
            }, self::$attrGrp)
                ->html('rgpd-info', '<p><a :attr>:content</a></p>', [
                    ':content' => t('Read the privacy policy'),
                    'href'     => $router->makeRoute($this->config->get('settings.rgpd_page')),
                    'target'   => '_blank'
            ]);
        }

        return $this;
    }

    public function passwordCurrentGroup(&$form)
    {
        $this->password($form, 'password', t('Password'));

        return $this;
    }

    public function passwordNewGroup(&$form)
    {
        $this->password(
            $form,
            'password_new',
            t('New Password'),
            $this->config->get('settings.password_show', true)
                ? [ 'onkeyup' => 'passwordPolicy(this)' ]
                : []
        );

        return $this;
    }

    public function passwordConfirmGroup(&$form)
    {
        $this->password($form, 'password_confirm', t('Confirmation of the new password'));

        return $this;
    }

    public function password(&$form, $id, $label, array $attr = [])
    {
        $form->group("$id-group", 'div', function ($form) use ($id, $label, $attr) {
            $form->label("$id-label", $label, [ 'for' => $id ])
                ->group("$id-flex", 'div', function ($form) use ($id, $attr) {
                    $form->password($id, [ 'class' => 'form-control' ] + $attr);
                    if ($this->config && $this->config->get('settings.password_show', true)) {
                        $form->html("{$id}_show", '<button:attr>:content</button>', [
                            'class'        => 'btn btn-toogle-password',
                            'onclick'      => "togglePassword(this, '$id')",
                            'type'         => 'button',
                            ':content'     => '<i class="fa fa-eye eyeIcon" aria-hidden="true"></i>',
                            'data-tooltip' => t('Show/Hide password'),
                            'aria-label'   => t('Show/Hide password')
                        ]);
                    }
                }, [ 'class' => 'form-group-flex' ]);
        }, self::$attrGrp);
    }

    public function informationsCreateFieldset()
    {
        return $this->group('informations-fieldset', 'fieldset', function ($form) {
            $form->legend('informations-legend', t('Information'));
            $this->usernameGroup($form)
                    ->emailGroup($form);
        });
    }

    public function informationsFieldset()
    {
        return $this->group('informations-fieldset', 'fieldset', function ($form) {
            $form->legend('informations-legend', t('Information'));
            $this->usernameGroup($form)
                    ->emailGroup($form)
                    ->passwordCurrentGroup($form);
        });
    }

    public function profilFieldset()
    {
        return $this->group('profil-fieldset', 'fieldset', function ($form) {
            $form->legend('profil-legend', t('Profile'));
            $this->pictureGroup($form)
                    ->bioGroup($form)
                    ->nameGroup($form)
                    ->firstnameGroup($form);
        });
    }

    public function passwordFieldset()
    {
        return $this->group('password-fieldset', 'fieldset', function ($form) {
            $form->legend('password-legend', t('Password'));
            $this->passwordNewGroup($form)
                    ->passwordConfirmGroup($form)
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

            $content = '<li data-pattern=".{' . $length . ',}">' . t('Minimum length') . ' : ' . $length . '</li>'
                . '<li data-pattern="(?=.*[A-Z]){' . $upper . ',}">' . t('Number of uppercase characters') . ' : ' . $upper . '</li>'
                . '<li data-pattern="(?=.*\d){' . $digit . ',}">' . t('Number of numeric characters') . ' : ' . $digit . '</li>'
                . '<li data-pattern="(?=.*\W){' . $special . ',}">' . t('Number of special characters') . ' : ' . $special . '</li>';

            $form->html('password_policy', '<ul:attr>:content</ul>', [
                ':content' => $content,
            ]);
        }

        return $this;
    }

    public function activedFieldset()
    {
        return $this->group('actived-fieldset', 'fieldset', function ($form) {
            $form->legend('actived-legend', t('Status'))
                    ->group('actived-group', 'div', function ($form) {
                        $form->checkbox('actived', [ 'checked' => $this->values[ 'actived' ] ])
                        ->label('actived-label', '<span class="ui"></span> ' . t('Active'), [
                            'for' => 'actived' ]);
                    }, self::$attrGrp);
        });
    }

    public function rolesFieldset(array $roles)
    {
        return $roles
            ? $this->group('role-fieldset', 'fieldset', function ($form) use ($roles) {
                $form->legend('role-legend', t('User Roles'));
                foreach ($roles as $role) {
                    $attrRole = [
                        'checked'  => $role[ 'role_id' ] <= 2 || isset($this->values[ 'roles' ][ $role[ 'role_id' ] ]),
                        'disabled' => $role[ 'role_id' ] <= 2,
                        'id'       => "role_{$role[ 'role_id' ]}",
                        'value'    => $role[ 'role_label' ]
                    ];
                    $form->group('role_' . $role[ 'role_id' ] . '-group', 'div', function ($form) use ($role, $attrRole) {
                        $form->checkbox("roles[{$role[ 'role_id' ]}]", $attrRole)
                            ->label(
                                'role_' . $role[ 'role_id' ] . '-label',
                                '<span class="ui"></span>'
                                . '<span class="badge-role" style="background-color: ' . $role[ 'role_color' ] . '">'
                                . '<i class="' . $role[ 'role_icon' ] . '" aria-hidden="true"></i>'
                                . '</span> '
                                . t($role[ 'role_label' ]),
                                [ 'for' => "role_{$role[ 'role_id' ]}" ]
                            );
                    }, self::$attrGrp);
                }
            })
        : $this;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function submitForm($label = 'Save', $cancel = false)
    {
        $this->token('token_user_form')
            ->submit('submit', t($label), [ 'class' => 'btn btn-success' ]);
        if ($cancel) {
            $this->html('cancel', '<button:attr>:content</button>', [
                ':content' => t('Cancel'),
                'class'    => 'btn btn-danger',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ]);
        }

        return $this;
    }
}
