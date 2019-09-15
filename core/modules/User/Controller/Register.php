<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUser;

class Register extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes = dirname(__DIR__) . '/Config/routing-register.json';
        $this->pathViews  = dirname(__DIR__) . '/Views/';
    }

    public function create()
    {
        $data = [ 'username' => '', 'email' => '', 'password' => '', 'password_confirm' => '' ];

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.register.store')
            ], null, self::config()))->content($data);
        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('register-legend', t('User registration'));
            $form->username($formbuilder)
                ->email($formbuilder)
                ->passwordCurrent($formbuilder)
                ->passwordConfirm($formbuilder);
        })->submitForm();

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        $connect_url = self::config()->get('settings.connect_url', '');
        $url = self::router()->getRoute('user.login', [ ':url' => '/' . $connect_url ]);

        return self::template()
                ->view('page', [
                    'title_main' => t('Registration')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-register.php', $this->pathViews, [
                    'form'        => $form,
                    'url_relogin' => $url ]);
    }

    public function store($req)
    {
        $route     = self::router()->getRoute('user.register.create');
        $post      = $req->getParsedBody();
        $validator = (new Validator())
            ->setRules([
                'username'         => 'required|string|max:255|htmlsc',
                'email'            => 'required|email|htmlsc',
                'password'         => 'required|string|regex:' . self::user()->passwordPolicy(),
                'password_confirm' => 'required|string|equal:@password',
                'token_user_form'  => 'required|token'
            ])
            ->setInputs($post);

        $is_email    = self::user()->getUser($validator->getInput('email'));
        $is_username = self::query()->from('user')
                ->where('username', $validator->getInput('username'))->fetch();

        $this->container->callHook('register.store.validator', [ &$validator ]);

        if ($validator->isValid() && !$is_email && !$is_username) {
            $salt        = base64_encode(random_bytes(32));
            $passworHash = self::user()->hashSession($validator->getInput('password'), $salt);
            $data        = [
                'username'       => $validator->getInput('username'),
                'email'          => $validator->getInput('email'),
                'password'       => self::user()->hash($passworHash),
                'salt'           => $salt,
                'token_actived'  => Util::strRandom(30),
                'time_installed' => (string) time(),
                'timezone'       => 'Europe/Paris'
            ];

            $this->container->callHook('register.store.before', [ &$validator, &$data ]);
            self::query()->insertInto('user', array_keys($data))->values($data)->execute();
            $user = self::user()->getUserActived($data[ 'email' ], false);
            self::query()->insertInto('user_role', [ 'user_id', 'role_id' ])
                ->values([ $user[ 'user_id' ], 2 ])->execute();
            $this->sendMailRegister($data[ 'email' ]);
            $this->container->callHook('register.store.after', [ &$validator ]);

            return new Redirect($route);
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        if ($is_email) {
            $_SESSION[ 'messages' ][ 'errors' ][] = t('The :email email is unavailable.', [':email' => $validator->getInput('email')]);
            $_SESSION[ 'errors_keys' ][]          = 'email';
        }
        if ($is_username) {
            $_SESSION[ 'messages' ][ 'errors' ][] = t('The :name username is unavailable.', [':name' => $validator->getInput('username')]);
            $_SESSION[ 'errors_keys' ][]          = 'username';
        }

        return new Redirect($route);
    }

    public function activate($id, $token, $req)
    {
        if (!($user = self::user()->find($id))) {
            return $this->get404($req);
        }
        if ($user[ 'token_actived' ] !== $token) {
            return $this->get404($req);
        }

        $this->container->callHook('register.activate.before', [ $id ]);
        self::query()->update('user', [ 'token_actived' => '', 'actived' => true ])
            ->where('user_id', $id)->execute();
        $this->container->callHook('register.activate.after', [ $id ]);

        $_SESSION[ 'messages' ][ 'success' ] = [
            t('Your user account has just been activated, you can now login.')
        ];
        $route = self::router()->getRoute('user.login');

        return new Redirect($route);
    }

    protected function sendMailRegister($from)
    {
        $user    = self::user()->getUser($from);
        $url     = self::router()->getRoute('user.activate', [
            ':id'    => $user[ 'user_id' ],
            ':token' => $user[ 'token_actived' ]
        ]);
        $message = t('A user registration request has been made.')
            . t('You can now validate the creation of your user account by clicking on this link or by copying it to your browser: :url', [
                ':url' => $url
            ])
            . t('This link can only be used once.');

        $mail = (new Email())
            ->to(self::config()->get('settings.email'))
            ->from($from)
            ->subject(t('User registration'))
            ->message($message);

        if ($mail->send()) {
            $_SESSION[ 'messages' ][ 'success' ] = [
                t('An email with instructions to access your account has just been sent to you. Warning ! This can be in your junk mail.')
            ];
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = [ t('An error prevented your email from being sent.') ];
        }
    }
}
