<?php

namespace tests\unit\core\modules\User\Controller;

use Soosyze\Core\Modules\Template\Services\Templating;
use tests\unit\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testGetLogin(): void
    {
        $response = self::request('GET', '/user/login');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Templating::class, $response);

        $html = (string) $response;

        $this->assertStringContainsString(
            '<title>Connexion | Soosyze site</title>',
            $html
        );
        $this->assertStringContainsString(
            '<input name="email" type="email" class="form-control" maxlength="254" placeholder="exemple@mail.com" required id="email">',
            $html
        );
        $this->assertStringContainsString(
            '<input name="password" type="password" class="form-control" id="password">',
            $html
        );
        $this->assertStringContainsString(
            '<a href="http://localhost/user/relogin">Mot de passe oublié ?</a>',
            $html
        );
    }

    public function testPostLoginErrorEmail(): void
    {
        $response = self::request('POST', '/user/login', [
                'email'    => 'error',
                'password' => 'error',
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [ 'content-type' => [ 'application/json' ] ],
            $response->getHeaders()
        );
        $this->assertEquals(
            [
                'messages'    => [
                    'errors' => [
                        'email[email]'           => 'The email field must be a valid email address.',
                        'token_user_form[token]' => 'An error has occurred.'
                    ]
                ],
                'errors_keys' => [
                    'email',
                    'token_user_form'
                ]
            ],
            json_decode((string) $response->getBody(), true)
        );
    }
}
