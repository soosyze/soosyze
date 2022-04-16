<?php

declare(strict_types=1);

namespace SoosyzeCore\User\Hook;

use Soosyze\Components\Router\Router;
use Soosyze\Config;
use SoosyzeCore\Template\Services\Block as ServiceBlock;
use SoosyzeCore\User\Form\FormUser;
use SoosyzeCore\User\Hook\Config as HookConfig;
use SoosyzeCore\User\Services\User;

class Block implements \SoosyzeCore\Block\BlockInterface
{
    /**
     * @var string
     */
    private const PATH_VIEWS = __DIR__ . '/../Views/';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var User
     */
    private $user;

    public function __construct(Config $config, Router $router, User $user)
    {
        $this->config = $config;
        $this->router = $router;
        $this->user   = $user;
    }

    public function hookBlockCreateFormData(array &$blocks): void
    {
        $blocks[ 'user.login' ] = [
            'description' => t('Displays a login form when the user is not logged in.'),
            'hook'        => 'user.login',
            'icon'        => 'fas fa-sign-in-alt',
            'no_content'  => t('The login form is displayed only for users who are not logged in.'),
            'path'        => self::PATH_VIEWS,
            'title'       => t('Sign in form'),
            'tpl'         => 'components/block/user-login.php'
        ];
    }

    public function hookUserLogin(ServiceBlock $tpl, ?array $options): ?ServiceBlock
    {
        if ($this->user->isConnected()) {
            return null;
        }

        $form = (new FormUser([
                'action' => $this->router->generateUrl('user.login.check', [ 'url' => '' ]),
                'method' => 'post'
                ], null, $this->config));

        $form->group('login-fieldset', 'fieldset', function ($formbuilder) use ($form) {
            $formbuilder->legend('login-legend', t('User login'));
            $form->emailGroup($formbuilder)
                ->passwordCurrentGroup($formbuilder);
        })
            ->submitForm(t('Sign in'));

        return $tpl->addVars([
                'form'             => $form,
                'granted_register' => $this->config->get('settings.user_register', HookConfig::USER_REGISTER),
                'granted_relogin'  => $this->config->get('settings.user_relogin', HookConfig::USER_RELOGIN),
                'url_relogin'      => $this->router->generateUrl('user.relogin', [
                    'url' => ''
                ]),
                'url_register'     => $this->router->generateUrl('user.register.create')
        ]);
    }
}
