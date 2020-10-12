<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Email\Email;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUser;

class Login extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function login($url, $req)
    {
        if (self::user()->isConnectUrl($url)) {
            return $this->get404($req);
        }

        if (self::user()->isConnected()) {
            return new Redirect(self::router()->getRoute('user.account'));
        }

        $values = [];
        $this->container->callHook('login.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values += $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.login.check', [ ':url' => $url ])
            ], null, self::config()))
            ->setValues($values);

        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('login-legend', t('User login'));
            $form->email($formbuilder)
                ->passwordCurrent($formbuilder);
        })->submitForm(t('Sign in'));

        $this->container->callHook('login.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Sign in')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'user/content-login-login.php', $this->pathViews, [
                    'form'             => $form,
                    'url_relogin'      => self::router()->getRoute('user.relogin', [
                        ':url' => $url
                    ]),
                    'url_register'     => self::router()->getRoute('user.register.create'),
                    'granted_relogin'  => self::config()->get('settings.user_relogin'),
                    'granted_register' => self::config()->get('settings.user_register')
        ]);
    }

    public function loginCheck($url, $req)
    {
        if (self::user()->isConnectUrl($url)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'email'           => 'required|email|max:254',
                'password'        => 'required|string',
                'token_user_form' => 'required|token'
            ])
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            self::auth()->login($validator->getInput('email'), $validator->getInput('password'));
        }

        if ($user = self::user()->isConnected()) {
            $route = $this->getRedirectLogin($req);
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputs();
            $_SESSION[ 'messages' ][ 'errors' ] = [ t('E-mail or password not recognized.') ];

            $route = self::router()->getRoute('user.login', [
                ':url' => $url
            ]);
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
        if (self::user()->isConnectUrl($url)) {
            return $this->get404($req);
        }

        $values = [];
        $this->container->callHook('relogin.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values += $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.relogin.check', [ ':url' => $url ])
            ]))
            ->setValues($values);

        $form->group('login-fieldset', 'fieldset', function ($formBuilder) use ($form) {
            $form->email($formBuilder);
        })->submitForm();

        $this->container->callHook('relogin.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Request a new password')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'user/content-login-relogin.php', $this->pathViews, [
                    'form'      => $form,
                    'url_login' => self::router()->getRoute('user.login', [ ':url' => $url ])
        ]);
    }

    public function reloginCheck($url, $req)
    {
        if (self::user()->isConnectUrl($url)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'email'           => 'required|email|max:254',
                'token_user_form' => 'required|token'
            ])
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            $user = self::user()->getUserActived($validator->getInput('email'));

            if ($user) {
                $token = Util::strRandom();
                $timeReset = date_create('now ' . self::config()->get('settings.password_reset_timeout'));

                self::query()
                    ->update('user', [
                        'token_forget' => $token,
                        'time_reset'   =>  $timeReset->getTimestamp()
                    ])
                    ->where('email', $validator->getInput('email'))
                    ->execute();

                $urlReset = self::router()->getRoute('user.reset', [
                    ':id'    => $user[ 'user_id' ],
                    ':token' => $token
                ]);
                $message  = t('A request for renewal of the password has been made. You can now login by clicking on this link or by copying it to your browser:') . "\n";
                $message  .= '<a target="_blank" href="' . $urlReset . '" rel="noopener noreferrer" data-auth="NotApplicable">' . $urlReset . '</a>';

                $email = (new Email())
                    ->from(self::config()->get('settings.email'))
                    ->to($user[ 'email' ])
                    ->subject(t('New Password'))
                    ->message($message)
                    ->isHtml(true);

                if ($email->send()) {
                    $_SESSION[ 'messages' ][ 'success' ] = [
                        t('An email with instructions to access your account has just been sent to you. Warning ! This can be in your junk mail.')
                    ];

                    return new Redirect(self::router()->getRoute('user.login', [
                            ':url' => $url
                    ]));
                }

                $_SESSION[ 'messages' ][ 'errors' ] = [ t('An error prevented your email from being sent.') ];
            } else {
                $_SESSION[ 'messages' ][ 'errors' ] = [ t('Sorry, this email is not recognized.') ];
            }
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        }

        $_SESSION[ 'inputs' ] = $validator->getInputs();

        return new Redirect(self::router()->getRoute('user.relogin', [
                ':url' => $url
        ]));
    }

    public function resetUser($id, $token, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }
        if ($user[ 'token_forget' ] !== $token) {
            return $this->get404($req);
        }
        if ($user['time_reset'] < time()) {
            $_SESSION[ 'messages' ][ 'errors' ] = t('Password reset timeout');

            return $this->get404($req);
        }

        $pwd = time();

        self::query()
            ->update('user', [
                'password'     => self::auth()->hash($pwd),
                'token_forget' => '',
                'time_reset'   => null
            ])
            ->where('user_id', '==', $id)
            ->execute();

        self::auth()->login($user[ 'email' ], $pwd);

        return new Redirect(self::router()->getRoute('user.edit', [ ':id' => $id ]));
    }

    protected function getRedirectLogin($req)
    {
        if (($redirect = self::config()->get('settings.connect_redirect', ''))) {
            return (string) self::router()->makeRoute($redirect);
        }

        return self::router()->getRoute('user.account');
    }
}
