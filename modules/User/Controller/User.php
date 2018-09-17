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

        $content = [ 'mail' => '' ];
        if (isset($_SESSION[ 'inputs' ])) {
            $content = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('user.login.check');

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('user-login', 'fieldset', function ($form) use ($content) {
                $form->legend('legen-login', 'Connexion utilisateur')
            ->group('user-login-mail', 'div', function ($form) use ($content) {
                $form->label('labelMail', 'Email')
                ->email('mail', 'mail', [
                    'value'    => $content[ 'mail' ], 
                    'required' => 1, 
                    'class'    => 'form-control'
                ]);
            }, [ 'class' => 'form-group' ])
            ->group('user-login-password', 'div', function ($form) {
                $form->label('labelPassword', 'Password')
                ->password('pass', 'pass', [
                    'required' => 1, 
                    'class'    => 'form-control'
                ]);
            }, [ 'class' => 'form-group' ])
            ->token()
            ->submit('sumbit', 'Validez', [ 'class' => 'btn btn-success' ]);
            }, [ 'class' => 'form-group' ]);

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            unset($_SESSION[ 'errors' ]);
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

    public function loginCheck($r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'mail'  => 'required|email',
                'pass'  => 'required|string',
                'token' => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            self::user()->login($validator->getInput('mail'), $validator->getInput('pass'));
        }

        $user = self::user()->isConnected();

        if (!$user) {
            $_SESSION[ 'inputs' ] = $validator->getInputs();
            $_SESSION[ 'errors' ] = [ 'msg' => 'Désolé, nom d\'utilisateur ou mot de passe non reconnu.' ];
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
            ->group('user-relogin', 'fieldset', function ($form) use ($content) {
                $form->group('user-edit-email', 'div', function ($form) use ($content) {
                    $form->label('labelLogin', 'Email')
                ->email('email', 'email', [
                    'required' => 1,
                    'value'    => $content[ 'email' ],
                    'class'    => 'form-control'
                ]);
                }, [ 'class' => 'form-group' ])
            ->token()
            ->submit('sumbit', 'Validez', [ 'class' => 'btn btn-success' ]);
            }, [ 'class' => 'form-group' ]);

        if (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }
        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            $form->addAttr('email', [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ]);
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

    public function reloginCheck($r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email' => 'required|email',
                'token' => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $query = self::query()
                ->from('user')
                ->where('email', $validator->getInput('email'))
                ->fetch();

            if ($query) {
                $token = hash('sha256', $query[ 'email' ] . $query[ 'timeInstalled' ] . time());

                $dataUser = [ 'forgetPass' => $token ];

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
                $adress = self::core()->getConfig('settings.email', $query[ 'email' ]);
                $isSend = $email->to($adress)
                    ->from($query[ 'email' ])
                    ->subject('Remplacement de mot de passe')
                    ->message($message)
                    ->send();

                if ($isSend) {
                    $_SESSION[ 'success' ] = [
                        'msg' => 'Un mail avec les instructions pour accéder à votre compte vient de vous être envoyé. '
                        . 'Attention ! Il peut être dans vos courriers indésirables.'
                    ];

                    $route = self::router()->getRoute('user.login');

                    return new Redirect($route);
                } else {
                    $_SESSION[ 'inputs' ] = $validator->getInputs();
                    $_SESSION[ 'errors' ] = [ 'msg' => 'Impossible d\'envoyer le mail.' ];
                }
            } else {
                $_SESSION[ 'inputs' ] = $validator->getInputs();
                $_SESSION[ 'errors' ] = [ 'msg' => 'Désolé, ce mail n\'est pas reconnu par le site.' ];
            }
        } else {
            $_SESSION[ 'inputs' ] = $validator->getInputs();
            $_SESSION[ 'errors' ] = $validator->getErrors();
        }

        $route = self::router()->getRoute('user.relogin');

        return new Redirect($route);
    }

    public function resetUser($id, $token)
    {
        $query = self::query()
            ->from('user')
            ->where('user_id', '==', $id)
            ->fetch();

        if (!$query) {
            return $this->get404();
        }

        if ($query[ 'forgetPass' ] != $token) {
            return $this->get404();
        }

        self::user()->relogin($query[ 'email' ], $query[ 'password' ]);

        $route = self::router()->getRoute('user.edit', [ ':id' => $id ]);

        return new Redirect($route);
    }

    public function views($id)
    {
        $query = self::query()
            ->from('user')
            ->where('user_id', '==', $id)
            ->fetch();

        if (!$query) {
            return $this->get404();
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-user" aria-hidden="true"></i> Voir le profil utilisateur'
                ])
                ->render('page.content', 'page-user-view.php', VIEWS_USER, [
                    'user' => $query
        ]);
    }

    public function edit($id)
    {
        $query = self::query()
            ->from('user')
            ->where('user_id', '==', $id)
            ->fetch();

        if (!$query) {
            return $this->get404();
        }

        if (isset($_SESSION[ 'inputs' ])) {
            $query = array_merge($query, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('user.edit.check', [ ':id' => $id ]);

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('user-edit-information', 'fieldset', function ($form) use ($query) {
                $form->legend('legen-edit-information', 'Informations')
                ->group('user-edit-email', 'div', function ($form) use ($query) {
                    $form->label('label-email', 'Email')
                    ->email('email', 'email', [ 'value' => $query[ 'email' ], 'class' => 'form-control' ]);
                }, [ 'class' => 'form-group' ])
                ->group('user-edit-name', 'div', function ($form) use ($query) {
                    $form->label('label-name', 'Nom')
                    ->text('name', 'name', [ 'value' => $query[ 'name' ],
                        'class' => 'form-control' ]);
                }, [ 'class' => 'form-group' ])
                ->group('user-edit-firstname', 'div', function ($form) use ($query) {
                    $form->label('label-firstname', 'Prénom')
                    ->text('firstname', 'firstname', [ 'value' => $query[ 'firstname' ],
                        'class' => 'form-control' ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('user-edit-newpassword', 'fieldset', function ($form) {
                $form->legend('legen-edit-newpassword', 'Mot de passe')
                ->group('user-edit-newpassword', 'div', function ($form) {
                    $form->label('label-newpassword', 'Nouveau mot de passe')
                    ->password('newpassword', 'newpassword', [ 'class' => 'form-control' ]);
                }, [ 'class' => 'form-group' ])
                ->group('user-edit-confirmpassword', 'div', function ($form) {
                    $form->label('label-confirmpassword', 'Confirmation du nouveau mot de passe')
                    ->password('confirmpassword', 'confirmpassword', [
                        'class' => 'form-control' ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('sumbit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        if (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }
        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-user" aria-hidden="true"></i> Edition de l\'utilisateur'
                ])
                ->render('page.content', 'page-user-edit.php', VIEWS_USER, [
                    'form' => $form
        ]);
    }

    public function editCheck($id, $r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email'           => 'required|email',
                'name'            => 'required|string',
                'firstname'       => 'required|string',
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
            $_SESSION[ 'success' ] = [ 'msg' => 'Configuration Enregistré' ];
        } else {
            $_SESSION[ 'inputs' ]      = $validator->getInputs();
            $_SESSION[ 'errors' ]      = $validator->getErrors();
            $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();
        }

        $route = self::router()->getRoute('user.edit', [ ':id' => $id ]);

        return new Redirect($route);
    }
}
