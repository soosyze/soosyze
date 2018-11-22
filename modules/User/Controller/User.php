<?php

namespace User\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Email\Email;

define("VIEWS_USER", MODULES_CORE . 'User' . DS . 'Views' . DS);
define("CONFIG_USER", MODULES_CORE . 'User' . DS . 'Config' . DS);

class User extends \Soosyze\Controller
{
    protected $pathRoutes = CONFIG_USER . 'routing.json';

    protected $pathServices = CONFIG_USER . 'service.json';

    public function login()
    {
        if (($user = self::user()->isConnected())) {
            $route = self::router()->getRoute('user.views', [ ':id' => $user[ 'user_id' ] ]);

            return new Redirect($route);
        }

        $content = [ 'email' => '' ];
        if (isset($_SESSION[ 'inputs' ])) {
            $content = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('user.login.check');

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('user-login-fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('user-login-legend', 'Connexion utilisateur')
                ->group('user-login-email-group', 'div', function ($form) use ($content) {
                    $form->label('user-login-email-label', 'E-mail')
                    ->email('email', 'email', [
                        'class'     => 'form-control',
                        'maxlength' => 254,
                        'required'  => 1,
                        'value'     => $content[ 'email' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('user-login-password-group', 'div', function ($form) {
                    $form->label('user-login-password-label', 'Mot de passe')
                    ->password('password', 'password', [
                        'class'    => 'form-control',
                        'required' => 1
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('sumbit', 'Validez', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        $url = self::router()->getRoute('user.relogin');

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-user" aria-hidden="true"></i> Connexion'
                ])
                ->render('page.content', 'page-login.php', VIEWS_USER, [
                    'form'        => $form,
                    'url_relogin' => $url ]);
    }

    public function loginCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email'    => 'required|email|max:254',
                'password' => 'required|string',
                'token'    => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            self::user()->login($validator->getInput('email'), $validator->getInput('password'));
        }

        if (!($user = self::user()->isConnected())) {
            $_SESSION[ 'inputs' ] = $validator->getInputs();
            $_SESSION[ 'errors' ] = [ 'Désolé, e-mail ou mot de passe non reconnu.' ];
            $route                = self::router()->getRoute('user.login');
        } else {
            $route = self::router()->getRoute('user.views', [ ':id' => $user[ 'user_id' ] ]);
        }

        return new Redirect($route);
    }

    public function logout()
    {
        session_destroy();
        session_unset();

        return new Redirect('index.php');
    }

    public function relogin()
    {
        $action = self::router()->getRoute('user.relogin.check');

        $content = [ 'email' => '' ];
        if (isset($_SESSION[ 'inputs' ])) {
            $content = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('user-relogin-fieldset', 'fieldset', function ($form) use ($content) {
                $form->group('user-relogin-email-group', 'div', function ($form) use ($content) {
                    $form->label('user-relogin-email-label', 'E-mail')
                    ->email('email', 'email', [
                        'class'     => 'form-control',
                        'maxlength' => 254,
                        'required'  => 1,
                        'value'     => $content[ 'email' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('sumbit', 'Validez', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        $url = self::router()->getRoute('user.login');

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-user" aria-hidden="true"></i> Demander un nouveau mot de passe'
                ])
                ->render('page.content', 'page-relogin.php', VIEWS_USER, [
                    'form'      => $form,
                    'url_login' => $url
        ]);
    }

    public function reloginCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email' => 'required|email|max:254',
                'token' => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $query = self::query()
                ->from('user')
                ->where('email', $validator->getInput('email'))
                ->fetch();

            if ($query) {
                $token = hash('sha256', $query[ 'email' ] . $query[ 'time_installed' ] . time());

                $dataUser = [ 'forget_pass' => $token ];

                self::query()
                    ->update('user', $dataUser)
                    ->where('email', $validator->getInput('email'))
                    ->execute();

                $url = self::router()->getRoute('user.reset', [
                    ':id'    => $query[ 'user_id' ],
                    ':token' => $token
                ]);

                $message = "
Une demande de renouvellement de mot de passe a été faite.

Vous pouvez désormais vous identifier en cliquant sur ce lien ou en le
copiant dans votre navigateur : $url";

                $email  = new Email();
                $adress = self::config()->get('settings.email', $query[ 'email' ]);
                $isSend = $email->to($adress)
                    ->from($query[ 'email' ])
                    ->subject('Remplacement de mot de passe')
                    ->message($message)
                    ->send();

                if ($isSend) {
                    $_SESSION[ 'success' ] = [
                        'Un email avec les instructions pour accéder à votre compte vient de vous être envoyé. 
                        Attention ! Il peut être dans vos courriers indésirables.'
                    ];

                    $route = self::router()->getRoute('user.login');

                    return new Redirect($route);
                } else {
                    $_SESSION[ 'inputs' ] = $validator->getInputs();
                    $_SESSION[ 'errors' ] = [ 'Impossible d\'envoyer l\'email.' ];
                }
            } else {
                $_SESSION[ 'errors' ] = [ 'Désolé, cette e-mail n\'est pas reconnu par le site.' ];
            }
        } else {
            $_SESSION[ 'errors' ] = $validator->getErrors();
        }

        $_SESSION[ 'inputs' ] = $validator->getInputs();
        $route = self::router()->getRoute('user.relogin');

        return new Redirect($route);
    }

    public function resetUser($id, $token, $req)
    {
        if (!($query = self::user()->find($id))) {
            return $this->get404($req);
        }

        if ($query[ 'forget_pass' ] != $token) {
            return $this->get404($req);
        }

        self::user()->relogin($query[ 'email' ], $query[ 'password' ]);

        $route = self::router()->getRoute('user.edit', [ ':id' => $id ]);

        return new Redirect($route);
    }

    public function views($id, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-user" aria-hidden="true"></i> Voir le profil utilisateur'
                ])
                ->render('page.content', 'page-user-view.php', VIEWS_USER, [
                    'user' => $user
        ]);
    }

    public function edit($id, $req)
    {
        if (!($query = self::user()->find($id))) {
            return $this->get404($req);
        }

        if (isset($_SESSION[ 'inputs' ])) {
            $query = array_merge($query, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('user.edit.check', [ ':id' => $id ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('user-edit-information-fieldset', 'fieldset', function ($form) use ($query) {
                $form->legend('user-edit-information-legend', 'Informations')
                ->group('user-edit-email-group', 'div', function ($form) use ($query) {
                    $form->label('user-edit-email-label', 'E-mail')
                    ->email('email', 'email', [
                        'class'     => 'form-control',
                        'maxlength' => 254,
                        'required'  => 1,
                        'value'     => $query[ 'email' ]
                     ]);
                }, [ 'class' => 'form-group' ])
                ->group('user-edit-name-group', 'div', function ($form) use ($query) {
                    $form->label('user-edit-name-label', 'Nom')
                    ->text('name', 'name', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'value'     => $query[ 'name' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('user-edit-firstname-group', 'div', function ($form) use ($query) {
                    $form->label('user-edit-firstname-label', 'Prénom')
                    ->text('firstname', 'firstname', [
                        'class'     => 'form-control',
                        'maxlength' => 255,
                        'value'     => $query[ 'firstname' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('user-edit-newpassword-fieldset', 'fieldset', function ($form) {
                $form->legend('user-edit-newpassword-legend', 'Mot de passe')
                ->group('user-edit-newpassword-group', 'div', function ($form) {
                    $form->label('user-edit-newpassword-label', 'Nouveau mot de passe')
                    ->password('newpassword', 'newpassword', [ 'class' => 'form-control' ]);
                }, [ 'class' => 'form-group' ])
                ->group('confirmpassword-group', 'div', function ($form) {
                    $form->label('user-edit-confirmpassword-label', 'Confirmation du nouveau mot de passe')
                    ->password('confirmpassword', 'confirmpassword', [
                        'class' => 'form-control' ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('sumbit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-user" aria-hidden="true"></i> Édition de l\'utilisateur'
                ])
                ->render('page.content', 'page-user-edit.php', VIEWS_USER, [
                    'form' => $form
        ]);
    }

    public function editCheck($id, $req)
    {
        if (!self::user()->find($id)) {
            return $this->get404($req);
        }
                
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                /* max:254 RFC5321 - 4.5.3.1.3. */
                'email'           => 'required|email|max:254',
                'name'            => 'required|string|max:255|htmlsc',
                'firstname'       => 'required|string|max:255|htmlsc',
                'newpassword'     => '!required|string|equal:@confirmpassword',
                'confirmpassword' => '!required|string|equal:@newpassword',
                'token'           => 'required|token'
            ])
            ->setInputs($post);
        
        if ($validator->isValid()) {
            /* Prépare les donnée à mettre à jour. */
            $dataUser = [
                'name'      => $validator->getInput('name'),
                'firstname' => $validator->getInput('firstname'),
                'email'     => $validator->getInput('email')
            ];

            $isUpdateMdp = ($validator->getInput('newpassword') != '') && ($validator->getInput('newpassword') == $validator->getInput('confirmpassword'));
            /* En cas de modification du mot de passe et confirmation. */
            if ($isUpdateMdp) {
                /* Je vais chercher l'utilisateur et hash le mot de passe. */
                $user = self::user()->getUser($validator->getInput('email'));

                $dataUser[ 'password' ] = hash('sha256', $validator->getInput('newpassword') . $user[ 0 ][ 'salt' ]);
            }

            self::query()
                ->update('user', $dataUser)
                ->where('user_id', 1)
                ->execute();

            /* En cas de modification du mdp, je me reconnecte. */
            if ($isUpdateMdp) {
                self::user()->login($validator->getInput('email'), $validator->getInput('newpassword'));
            }

            $user                  = self::user()->getUser($validator->getInput('email'));
            /* Modification du token_user à revoir. */
            self::user()->relogin($validator->getInput('email'), $user[ 'password' ]);
            $_SESSION[ 'success' ] = [ 'Configuration Enregistrée' ];
        } else {
            $_SESSION[ 'inputs' ]      = $validator->getInputs();
            $_SESSION[ 'errors' ]      = $validator->getErrors();
            $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();
        }

        $route = self::router()->getRoute('user.edit', [ ':id' => $id ]);

        return new Redirect($route);
    }
}
