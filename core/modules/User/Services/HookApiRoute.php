<?php

namespace SoosyzeCore\User\Services;

class HookApiRoute
{
    /**
     * @var \Soosyze\Config
     */
    private $config;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($config, $router)
    {
        $this->config = $config;
        $this->router = $router;
    }

    public function hookApiRoute(array &$routes, $search, $exclude)
    {
        $values = [
            [
                'title' => t('Account'),
                'route' => 'user/account',
                'link'  => $this->router->getRoute('user.account')
            ], [
                'title' => t('Sign in'),
                'route' => 'user/login',
                'link'  => $this->router->getRoute('user.login', [
                    ':url' => $this->config->get('connect_url', '')
                ])
            ], [
                'title' => t('Request a new password'),
                'route' => 'user/relogin',
                'link'  => $this->router->getRoute('user.relogin', [
                    ':url' => $this->config->get('connect_url', '')
                ])
            ], [
                'title' => t('Sign out'),
                'route' => 'user/logout',
                'link'  => $this->router->getRoute('user.logout', [
                    ':url' => $this->config->get('connect_url', '')
                ])
            ], [
                'title' => t('Sign up'),
                'route' => 'user/register',
                'link'  => $this->router->getRoute('user.register.create')
            ]
        ];

        foreach ($values as $value) {
            if ($exclude && $exclude === $value[ 'title' ]) {
                continue;
            }
            if (strpos($value[ 'title' ], $search) !== false) {
                $routes[] = $value;
            }
        }
    }
}
