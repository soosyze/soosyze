<?php

namespace User\Form;

use Soosyze\Components\Form\FormBuilder;

class FormUser extends FormBuilder
{
    protected $content = [
        'username'  => '',
        'email'     => '',
        'name'      => '',
        'firstname' => '',
        'actived'   => '',
    ];

    protected static $attrGrp = [ 'class' => 'form-group' ];

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
                    'class'     => 'form-control',
                    'maxlength' => 254,
                    'placeholder' => 'exemple@mail.com',
                    'required'  => 1,
                    'value'     => $this->content[ 'email' ]
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
            $form->label('user-password-label', 'Mot de passe')
                ->password('password', 'password', [ 'class' => 'form-control' ]);
        }, self::$attrGrp);

        return $this;
    }

    public function passwordNew(&$form)
    {
        $form->group('user-password_new-group', 'div', function ($form) {
            $form->label('user-password_new-label', 'Nouveau mot de passe')
                ->password('password_new', 'password_new', [ 'class' => 'form-control' ]);
        }, self::$attrGrp);

        return $this;
    }

    public function passwordConfirm(&$form)
    {
        $form->group('user-password_confirm-group', 'div', function ($form) {
            $form->label('user-password_confirm-label', 'Confirmation du nouveau mot de passe')
                ->password('password_confirm', 'password_confirm', [ 'class' => 'form-control' ]);
        }, self::$attrGrp);

        return $this;
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
                    ->passwordCurrent($form)
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
