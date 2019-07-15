<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Email\Email;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUser;

class Login extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing-login.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function formLogin()
    {
        if ($user = self::user()->isConnected()) {
            $route = self::router()->getRoute('user.account');

            return new Redirect($route);
        }

        $data = [ 'email' => '' ];
        $this->container->callHook('login.form.data', [ &$data ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.login.check')
            ], null, self::config()))->content($data);
        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('login-legend', 'Connexion utilisateur');
            $form->email($formbuilder)
                ->passwordCurrent($formbuilder);
        })->submitForm('Se connecter');

        $this->container->callHook('login.form', [ &$form, $data ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->view('page', [
                    'title_main' => '<i class="fa fa-user" aria-hidden="true"></i> Connexion'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-login.php', $this->pathViews, [
                    'form'             => $form,
                    'url_relogin'      => self::router()->getRoute('user.relogin'),
                    'url_register'     => self::router()->getRoute('user.register.create'),
                    'granted_relogin'  => empty($user) && self::config()->get('settings.user_relogin'),
                    'granted_register' => empty($user) && self::config()->get('settings.user_register')
        ]);
    }

    public function loginCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email'           => 'required|email|max:254',
                'password'        => 'required|string',
                'token_user_form' => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            self::user()->login($validator->getInput('email'), $validator->getInput('password'));
        }

        if ($user = self::user()->isConnected()) {
            self::query()
                ->update('user', [ 'time_access' => time() ])
                ->where('user_id', '==', $user[ 'user_id' ])
                ->execute();
            $route = self::router()->getRoute('user.account');
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'Désolé, e-mail ou mot de passe non reconnu.' ];
            $route                              = self::router()->getRoute('user.login');
        }

        return new Redirect($route);
    }

    public function logout()
    {
        session_destroy();
        session_unset();

        return new Redirect(self::router()->getBasePath());
    }

    public function relogin()
    {
        $data = [];
        $this->container->callHook('relogin.form.data', [ &$data ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.relogin.check')
            ]))->content($data);
        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $form->email($formbuilder);
        })->submitForm();

        $this->container->callHook('relogin.form', [ &$form, $data ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->view('page', [
                    'title_main' => '<i class="fa fa-user" aria-hidden="true"></i> Demander un nouveau mot de passe'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-relogin.php', $this->pathViews, [
                    'form'      => $form,
                    'url_login' => self::router()->getRoute('user.login')
        ]);
    }

    public function reloginCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email'           => 'required|email|max:254',
                'token_user_form' => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $query = self::user()->getUserActived($validator->getInput('email'));

            if ($query) {
                $token = hash('sha256', $query[ 'email' ] . $query[ 'time_installed' ] . time());

                self::query()
                    ->update('user', [ 'token_forget' => $token ])
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

                $adress = self::config()->get('settings.email', $query[ 'email' ]);
                $email  = (new Email())
                    ->to($adress)
                    ->from($query[ 'email' ])
                    ->subject('Remplacement de mot de passe')
                    ->message($message);

                if ($email->send()) {
                    $_SESSION[ 'messages' ][ 'success' ] = [
                        'Un email avec les instructions pour accéder à votre compte vient de vous être envoyé. 
                        Attention ! Il peut être dans vos courriers indésirables.'
                    ];

                    $route = self::router()->getRoute('user.login');

                    return new Redirect($route);
                } else {
                    $_SESSION[ 'messages' ][ 'errors' ] = [ 'Une erreur a empêché votre email d\'être envoyé.' ];
                }
            } else {
                $_SESSION[ 'messages' ][ 'errors' ] = [ 'Désolé, cette e-mail n’est pas reconnu par le site.' ];
            }
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        }

        $_SESSION[ 'inputs' ] = $validator->getInputs();
        $route                = self::router()->getRoute('user.relogin');

        return new Redirect($route);
    }

    public function resetUser($id, $token, $req)
    {
        if (!($user = self::user()->findActived($id))) {
            return $this->get404($req);
        }

        if ($user[ 'token_forget' ] != $token) {
            return $this->get404($req);
        }

        $time         = time();
        $passwordHash = self::user()->hashSession($time, $user[ 'salt' ]);
        $mdp          = self::user()->hash($passwordHash);
        self::query()
            ->update('user', [ 'password' => $mdp, 'token_forget' => '' ])
            ->where('user_id', '==', $id)
            ->execute();
        self::user()->login($user[ 'email' ], $time);

        $route = self::router()->getRoute('user.edit', [ ':id' => $id ]);

        return new Redirect($route);
    }
}
