<?php

namespace SoosyzeCore\User\Hook;

use SoosyzeCore\User\Form\FormUser;

class Block
{
    /**
     * @var \Soosyze\Config
     */
    private $config;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    /**
     * @var User
     */
    private $user;

    public function __construct($config, $router, $user)
    {
        $this->config = $config;
        $this->router = $router;
        $this->user   = $user;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function hookBlockCreateFormData(array &$blocks)
    {
        $blocks[ 'user.login' ] = [
            'hook'  => 'user.login',
            'path'  => $this->pathViews,
            'title' => 'Sign in',
            'tpl'   => 'components/block/user-login.php'
        ];
    }

    public function hookUserLogin($tpl, array $options)
    {
        if ($this->user->isConnected()) {
            return;
        }

        $form = (new FormUser([
                'method' => 'post',
                'action' => $this->router->getRoute('user.login.check', [ ':url' => '' ])
                ], null, $this->config));

        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('login-legend', t('User login'));
            $form->emailGroup($formbuilder)
                ->passwordCurrentGroup($formbuilder);
        })
            ->submitForm(t('Sign in'));

        return $tpl
                ->addVars([
                    'form'             => $form,
                    'url_relogin'      => $this->router->getRoute('user.relogin', [
                        ':url' => ''
                    ]),
                    'url_register'     => $this->router->getRoute('user.register.create'),
                    'granted_relogin'  => $this->config->get('settings.user_relogin'),
                    'granted_register' => $this->config->get('settings.user_register')
        ]);
    }
}
