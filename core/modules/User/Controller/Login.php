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
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function formLogin($url, $req)
    {
        $connect_url = self::config()->get('settings.connect_url', '');
        if (!empty($connect_url) && $url !== '/' . $connect_url) {
            return $this->get404($req);
        }
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
            'action' => self::router()->getRoute('user.login.check', [ ':url' => $url ])
            ], null, self::config()))->content($data);
        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('login-legend', t('User login'));
            $form->email($formbuilder)
                ->passwordCurrent($formbuilder);
        })->submitForm(t('Log in'));

        $this->container->callHook('login.form', [ &$form, $data ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->view('page', [
                    'title_main' => '<i class="fa fa-user" aria-hidden="true"></i> ' . t('Log in')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-login.php', $this->pathViews, [
                    'form'             => $form,
                    'url_relogin'      => self::router()->getRoute('user.relogin', [ ':url' => $url ]),
                    'url_register'     => self::router()->getRoute('user.register.create'),
                    'granted_relogin'  => empty($user) && self::config()->get('settings.user_relogin'),
                    'granted_register' => empty($user) && self::config()->get('settings.user_register')
        ]);
    }

    public function loginCheck($url, $req)
    {
        $connect_url = self::config()->get('settings.connect_url', '');
        if (!empty($connect_url) && $url !== '/' . $connect_url) {
            return $this->get404($req);
        }

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
            $route = $this->getRedirectLogin($req);
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ 'errors' ] = [ t('E-mail or password not recognized.') ];
            $route                              = self::router()->getRoute('user.login', [ ':url' => $url ]);
        }

        return new Redirect($route);
    }

    public function logout()
    {
        session_destroy();
        session_unset();

        return new Redirect(self::router()->getBasePath());
    }

    public function relogin($url, $req)
    {
        $connect_url = self::config()->get('settings.connect_url', '');
        if (!empty($connect_url) && $url !== '/' . $connect_url) {
            return $this->get404($req);
        }

        $data = [];
        $this->container->callHook('relogin.form.data', [ &$data ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.relogin.check', [ ':url' => $url ])
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
                    'title_main' => '<i class="fa fa-user" aria-hidden="true"></i> ' . t('Request a new password')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-relogin.php', $this->pathViews, [
                    'form'      => $form,
                    'url_login' => self::router()->getRoute('user.login', [ ':url' => $url ])
        ]);
    }

    public function reloginCheck($url, $req)
    {
        if (self::config()->has('settings.connect_url') && $url !== '/' . self::config()->get('settings.connect_url', '')) {
            return $this->get404($req);
        }

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

                $message = t('A request for renewal of the password has been made. You can now login by clicking on this link or by copying it to your browser :url', [':url' => $url]);

                $adress = self::config()->get('settings.email', $query[ 'email' ]);
                $email  = (new Email())
                    ->to($adress)
                    ->from($query[ 'email' ])
                    ->subject(t('New Password'))
                    ->message($message);

                if ($email->send()) {
                    $_SESSION[ 'messages' ][ 'success' ] = [
                        t('An email with instructions to access your account has just been sent to you. Warning ! This can be in your junk mail.')
                    ];

                    $route = self::router()->getRoute('user.login', [ ':url' => $url ]);

                    return new Redirect($route);
                } else {
                    $_SESSION[ 'messages' ][ 'errors' ] = [ t('An error prevented your email from being sent.') ];
                }
            } else {
                $_SESSION[ 'messages' ][ 'errors' ] = [ t('Sorry, this email is not recognized.') ];
            }
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        }

        $_SESSION[ 'inputs' ] = $validator->getInputs();
        $route                = self::router()->getRoute('user.relogin', [ ':url' => $url ]);

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
    
    protected function getRedirectLogin($req)
    {
        $redirect = self::config()->get('settings.connect_redirect', '');
        if ($redirect) {
            return (string) $req->getUri()->withQuery('?q=' . $redirect);
        }

        return self::router()->getRoute('user.account');
    }
}
