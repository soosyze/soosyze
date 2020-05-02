<?php

namespace SoosyzeCore\User\Controller;

use Soosyze\Components\Email\Email;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\User\Form\FormUser;

class Register extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews  = dirname(__DIR__) . '/Views/';
    }

    public function create()
    {
        $data = [ 'username' => '', 'email' => '', 'password' => '', 'password_confirm' => '', 'terms_of_service' => false, 'rgpd' => false ];

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormUser([
            'method' => 'post',
            'action' => self::router()->getRoute('user.register.store')
            ], null, self::config()))->setValues($data);

        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('register-legend', t('User registration'));
            $form->username($formbuilder)
                ->email($formbuilder)
                ->passwordNew($formbuilder)
                ->passwordConfirm($formbuilder)
                ->passwordPolicy($formbuilder)
                ->eula($formbuilder, self::router());
        })->submitForm(t('Registration'));

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'class' => 'is-invalid' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        if (($connect_url = self::config()->get('settings.connect_url', ''))) {
            $connect_url = '/' . $connect_url;
        }
        $url = self::router()->getRoute('user.login', [ ':url' => $connect_url ]);

        return self::template()
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Registration')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-register.php', $this->pathViews, [
                    'form'        => $form,
                    'url_relogin' => $url ]);
    }

    public function store($req)
    {
        $route       = self::router()->getRoute('user.register.create');
        $validator   = (new Validator())->setInputs($req->getParsedBody());
        $is_email    = ($user = self::user()->getUser($validator->getInput('email')))
            ? $user[ 'email' ]
            : '';
        $is_username = ($user = self::query()->from('user')
                ->where('username', $validator->getInput('username'))->fetch())
            ? $user[ 'username' ]
            : '';
        $validator
            ->addInput('is_email', $is_email)
            ->addInput('is_username', $is_username)
            ->addInput('is_rgpd', self::config()->get('settings.rgpd_show', ''))
            ->addInput('is_terms_of_service', self::config()->get('settings.terms_of_service_show', ''))
            ->setRules([
                'username'         => 'required|string|max:255|!equal:@is_username|to_htmlsc',
                'email'            => 'required|string|email|!equal:@is_email|to_htmlsc',
                'password_new'     => 'required|string|regex:' . self::user()->passwordPolicy(),
                'password_confirm' => 'required|string|equal:@password_new',
                'rgpd'             => 'required_with:is_rgpd',
                'terms_of_service' => 'required_with:is_terms_of_service',
                'token_user_form'  => 'required|token'
            ])
            ->setLabel([
                'username'         => t('User name'),
                'email'            => t('E-mail'),
                'password_new'     => t('New Password'),
                'password_confirm' => t('Confirmation of the new password'),
                'rgpd'             => t('Accepter la politique de confidentialité'),
                'terms_of_service' => t('Accepter les conditions générale d\'utilisation')
        ]);

        $this->container->callHook('register.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data        = [
                'username'         => $validator->getInput('username'),
                'email'            => $validator->getInput('email'),
                'password'         => self::auth()->hash($validator->getInput('password_new')),
                'token_actived'    => Util::strRandom(30),
                'time_installed'   => (string) time(),
                'timezone'         => 'Europe/Paris',
                'terms_of_service' => (bool) $validator->hasInput('terms_of_service'),
                'rgpd'             => (bool) $validator->hasInput('rgpd'),
            ];

            $this->container->callHook('register.store.before', [ $validator, &$data ]);
            self::query()->insertInto('user', array_keys($data))->values($data)->execute();
            $user = self::user()->getUserActived($data[ 'email' ], false);
            self::query()->insertInto('user_role', [ 'user_id', 'role_id' ])
                ->values([ $user[ 'user_id' ], 2 ])->execute();
            $this->sendMailRegister($data[ 'email' ]);
            $this->container->callHook('register.store.after', [ $validator ]);

            return new Redirect($route);
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

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
