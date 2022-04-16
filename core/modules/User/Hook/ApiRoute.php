<?php

declare(strict_types=1);

namespace SoosyzeCore\User\Hook;

use Soosyze\Components\Router\Router;
use Soosyze\Config;

class ApiRoute implements \SoosyzeCore\System\ApiRouteInterface
{
    /**
     * @var string
     */
    private $connectUrl;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Config $config, Router $router)
    {
        /** @phpstan-var string $connectUrl */
        $connectUrl       = $config->get('connect_url', '');
        $this->connectUrl = $connectUrl;
        $this->router     = $router;
    }

    public function apiRoute(array &$routes, string $search, string $exclude, int $limit): void
    {
        $values = [
            [
                'link'  => $this->router->generateUrl('user.account'),
                'route' => 'user/account',
                'title' => t('My account')
            ], [
                'link'  => $this->router->generateUrl('user.login', [
                    'url' => $this->connectUrl
                ]),
                'route' => 'user/login',
                'title' => t('Sign in')
            ], [
                'link'  => $this->router->generateUrl('user.relogin', [
                    'url' => $this->connectUrl
                ]),
                'route' => 'user/relogin',
                'title' => t('Request a new password')
            ], [
                'link'  => $this->router->generateUrl('user.logout', [
                    'url' => $this->connectUrl
                ]),
                'route' => 'user/logout',
                'title' => t('Sign out')
            ], [
                'link'  => $this->router->generateUrl('user.register.create'),
                'route' => 'user/register',
                'title' => t('Sign up')
            ]
        ];

        foreach ($values as $value) {
            if ($exclude === $value[ 'title' ] || stristr($value[ 'title' ], $search) === false) {
                continue;
            }

            $routes[] = $value;
        }
    }
}
