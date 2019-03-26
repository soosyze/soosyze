<?php

namespace User\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use User\Form\FormUser;

class Register extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes = CONFIG_USER . 'routing-register.json';
    }

    public function register()
    {
        $data = [ 'username' => '', 'email' => '', 'password' => '', 'password_confirm' => '' ];

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.register.check')
            ]))->content($data);
        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('register-legend', 'Inscription utilisateur');
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

        $url = self::router()->getRoute('user.login');

        return self::template()
                ->view('page', [
                    'title_main' => 'Inscription'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-register.php', VIEWS_USER, [
                    'form'        => $form,
                    'url_relogin' => $url ]);
    }

    public function store($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'username'         => 'required|string|max:255|htmlsc',
                'email'            => 'required|email|htmlsc',
                'password'         => 'required|string',
                'password_confirm' => 'required|string|equal:@password',
                'token'            => 'required|token'
            ])
            ->setInputs($post);

        $is_email    = self::user()->getUser($validator->getInput('email'));
        $is_username = self::query()->from('user')
                ->where('username', $validator->getInput('username'))->fetch();

        $this->container->callHook('register.store.validator', [ &$validator ]);

        if ($validator->isValid() && !$is_email && !$is_username) {
            $salt        = md5(time());
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

            $route = self::router()->getRoute('user.register');

            return new Redirect($route);
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        if ($is_email) {
            $_SESSION[ 'messages' ][ 'errors' ][] = 'L\'email <i>' . $validator->getInput('email') . '</i> est indisponible.';
            $_SESSION[ 'errors_keys' ][]          = 'email';
        }
        if ($is_username) {
            $_SESSION[ 'messages' ][ 'errors' ][] = 'Le nom d\'utilisateur <i>' . $validator->getInput('username') . '</i> est indisponible.';
            $_SESSION[ 'errors_keys' ][]          = 'username';
        }
        $route = self::router()->getRoute('user.register');

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
            'Votre compte utilisateur vient d\'être activé, vous pouvez désormais vous connecter.'
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
        $message = "
Une demande d'inscription utilisateur a été faite.

Vous pouvez désormais valider la création de votre compte utilisateur en cliquant sur ce lien ou en le
copiant dans votre navigateur : $url
    
Ce lien ne peut être utilisé qu'une seule fois.";

        $mail = (new Email())
            ->to(self::config()->get('settings.email'))
            ->from($from)
            ->subject('Inscription utilisateur')
            ->message($message);

        if ($mail->send()) {
            $_SESSION[ 'messages' ][ 'success' ] = [
                'Un email avec les instructions pour accéder à votre compte vient de vous être envoyé. '
                . 'Attention ! Il peut être dans vos courriers indésirables.'
            ];
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'Une erreur a empêché votre email d\'être envoyé.' ];
        }
    }
}
