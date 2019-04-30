<?php

namespace User\Form;

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
            $form->label('user-username-label', 'Nom utilisateur')
                ->text('username', 'username', [
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
            $form->label('user-email-label', 'E-mail')
                ->email('email', 'email', [
                    'class'       => 'form-control',
                    'maxlength'   => 254,
                    'placeholder' => 'exemple@mail.com',
                    'required'    => 1,
                    'value'       => $this->content[ 'email' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function picture(&$form)
    {
        $form->group('user-picture-group', 'div', function ($form) {
            $form->label('user-picture-label', 'Image', [
                'for' => 'file-name-picture',
                'data-tooltip' => '200ko maximum. Extensions autorisées : jpeg, jpg, png, gif.'
            ]);
            $this->file->inputFile('picture', $form, $this->content[ 'picture' ]);
        }, self::$attrGrp);

        return $this;
    }

    public function bio(&$form)
    {
        $form->group('system-description-group', 'div', function ($form) {
            $form->label('system-bio-label', 'Biographie', [
                    'data-tooltip' => 'Décrivez-vous en 255 caractères maximum.'
                ])
                ->textarea('bio', 'bio', $this->content[ 'bio' ], [
                    'class'       => 'form-control',
                    'maxlength'   => 255,
                    'placeholder' => 'Décrivez-vous en 255 caractères maximum.',
                    'rows'        => 3,
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function name(&$form)
    {
        $form->group('user-name-group', 'div', function ($form) {
            $form->label('user-name-label', 'Nom')
                ->text('name', 'name', [
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
            $form->label('user-firstname-label', 'Prénom')
                ->text('firstname', 'firstname', [
                    'class'     => 'form-control',
                    'maxlength' => 255,
                    'value'     => $this->content[ 'firstname' ]
            ]);
        }, self::$attrGrp);

        return $this;
    }

    public function passwordCurrent(&$form)
    {
        $form->group('user-password-group', 'div', function ($form) {
            $form->label('user-password-label', 'Mot de passe');
            $this->password($form, 'password');
        }, self::$attrGrp);

        return $this;
    }

    public function passwordNew(&$form)
    {
        $form->group('user-password_new-group', 'div', function ($form) {
            $form->label('user-password_new-label', 'Nouveau mot de passe');
            $this->password($form, 'password_new');
        }, self::$attrGrp);

        return $this;
    }

    public function passwordConfirm(&$form)
    {
        $form->group('user-password_confirm-group', 'div', function ($form) {
            $form->label('user-password_confirm-label', 'Confirmation du nouveau mot de passe');
            $this->password($form, 'password_confirm');
        }, self::$attrGrp);

        return $this;
    }
    
    public function password(&$form, $id)
    {
        $form->group("user-$id-group", 'div', function ($form) use ($id) {
            $form->password($id, $id, [ 'class' => 'form-control' ]);
            if ($this->config && $this->config->get('settings.password_show', true)) {
                $form->html('password_show', '<button:css:attr>:_content</button>', [
                    'class'        => 'btn-toogle-password',
                    'onclick'      => "togglePassword(this, '$id')",
                    'type'         => 'button',
                    '_content'     => '<i id="eyeIcon" class="fa fa-eye"></i>',
                    'data-tooltip' => 'Afficher/Cacher le mot de passe'
                ]);
            }
        }, [ 'class' => 'form-group-flex' ]);
    }

    public function fieldsetInformationsCreate()
    {
        return $this->group('user-informations-fieldset', 'fieldset', function ($form) {
            $form->legend('user-informations-legend', 'Informations');
            $this->username($form)
                    ->email($form)
                    ->name($form)
                    ->firstname($form);
        });
    }

    public function fieldsetInformations()
    {
        return $this->group('user-informations-fieldset', 'fieldset', function ($form) {
            $form->legend('user-informations-legend', 'Informations');
            $this->username($form)
                    ->email($form)
                    ->passwordCurrent($form);
        });
    }

    public function fieldsetProfil()
    {
        return $this->group('user-profil-fieldset', 'fieldset', function ($form) {
            $form->legend('user-informations-legend', 'Profil');
            $this->picture($form)
                    ->bio($form)
                    ->name($form)
                    ->firstname($form);
        });
    }

    public function fieldsetPassword()
    {
        return $this->group('user-password-fieldset', 'fieldset', function ($form) {
            $form->legend('user-password-legend', 'Mot de passe');
            $this->passwordNew($form)
                    ->passwordConfirm($form);
        });
    }

    public function fieldsetActived()
    {
        return $this->group('user-actived-fieldset', 'fieldset', function ($form) {
            $form->legend('user-actived-legend', 'Statut')
                    ->group('user-actived-fieldset', 'div', function ($form) {
                        $form->checkbox('actived', 'actived', [ 'checked' => $this->content[ 'actived' ] ])
                        ->label('user-actived-label', '<span class="ui"></span> Actif', [
                            'for' => 'actived' ]);
                    }, self::$attrGrp);
        });
    }

    public function fieldsetRoles($roles, $roles_user = [])
    {
        return $this->group('user-role-fieldset', 'fieldset', function ($form) use ($roles, $roles_user) {
            $form->legend('user-role-legend', 'Role utilisateur');
            foreach ($roles as $role) {
                $attrRole = [
                        'checked'  => in_array($role[ 'role_id' ], $roles_user),
                        'value'    => $role[ 'role_id' ],
                        'disabled' => $role[ 'role_id' ] <= 2
                    ];
                $form->group('user-role-' . $role[ 'role_id' ] . '-group', 'div', function ($form) use ($role, $attrRole) {
                    $form->checkbox('role[' . $role[ 'role_id' ] . ']', 'role-' . $role[ 'role_id' ], $attrRole)
                            ->label(
                                'user-role-' . $role[ 'role_id' ] . '-label',
                                '<span class="ui"></span>'
                                . '<span class="badge-role" style="background-color: ' . $role[ 'role_color' ] . '"></span> '
                                . $role[ 'role_label' ],
                                [ 'for' => 'role-' . $role[ 'role_id' ]
                                ]
                        );
                }, self::$attrGrp);
            }
        });
    }

    /**
     * @param  type  $content
     * @return $this
     */
    public function submitForm()
    {
        return $this->token()
                ->submit('sumbit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
    }
}
