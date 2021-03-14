<?php

namespace SoosyzeCore\User\Hook;

class ApiRoute implements \SoosyzeCore\System\ApiRouteInterface
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

    public function apiRoute(array &$routes, $search, $exclude, $limit)
    {
        $values = [
            [
                'link'  => $this->router->getRoute('user.account'),
                'route' => 'user/account',
                'title' => t('My account')
            ], [
                'link'  => $this->router->getRoute('user.login', [
                    ':url' => $this->config->get('connect_url', '')
                ]),
                'route' => 'user/login',
                'title' => t('Sign in')
            ], [
                'link'  => $this->router->getRoute('user.relogin', [
                    ':url' => $this->config->get('connect_url', '')
                ]),
                'route' => 'user/relogin',
                'title' => t('Request a new password')
            ], [
                'link'  => $this->router->getRoute('user.logout', [
                    ':url' => $this->config->get('connect_url', '')
                ]),
                'route' => 'user/logout',
                'title' => t('Sign out')
            ], [
                'link'  => $this->router->getRoute('user.register.create'),
                'route' => 'user/register',
                'title' => t('Sign up')
            ]
        ];

        foreach ($values as $value) {
            if ($exclude === $value[ 'title' ]) {
                continue;
            }

            if (stristr($value[ 'title' ], $search) !== false) {
                $routes[] = $value;
            }
        }
    }
}
