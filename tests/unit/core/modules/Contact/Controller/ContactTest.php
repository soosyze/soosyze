<?php

namespace tests\unit\core\modules\Contact\Controller;

use Soosyze\Core\Modules\Template\Services\Templating;
use tests\unit\WebTestCase;

class ContactTest extends WebTestCase
{
    public function testGetContactForm(): void
    {
        $response = self::request('GET', '/contact');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Templating::class, $response);

        $html = (string) $response;

        $this->assertStringContainsString(
            '<input name="name" type="text" class="form-control" required id="name">',
            $html
        );
        $this->assertStringContainsString(
            '<input name="email" type="email" class="form-control" required id="email">',
            $html
        );
        $this->assertStringContainsString(
            '<input name="object" type="text" class="form-control" required id="object">',
            $html
        );
        $this->assertStringContainsString(
            '<textarea name="message" class="form-control" required rows="8" id="message"></textarea>',
            $html
        );
        $this->assertStringContainsString(
            '<input name="copy" type="checkbox" id="copy">',
            $html
        );
        $this->assertStringContainsString(
            '<input name="submit" type="submit" class="btn btn-success" id="submit" value="Envoyer le message">',
            $html
        );
    }

    public function testPostContactFormError(): void
    {
        $response = self::request('POST', '/contact');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [ 'content-type' => [ 'application/json' ] ],
            $response->getHeaders()
        );
        $this->assertEquals(
            [
                'messages'    => [
                    'errors' => [
                        'name[required]'          => 'The Nom field is required.',
                        'email[required]'         => 'The E-mail field is required.',
                        'object[required]'        => 'The Objet field is required.',
                        'message[required]'       => 'The Message field is required.',
                        'token_contact[required]' => 'The token_contact field is required.',
                    ]
                ],
                'errors_keys' => [
                    'name',
                    'email',
                    'object',
                    'message',
                    'token_contact',
                ]
            ],
            json_decode((string) $response->getBody(), true)
        );
    }

    public function testPostContactForm(): void
    {
        $html  = self::request('GET', '/contact');
        $this->assertInstanceOf(Templating::class, $html);

        $token = $this->getToken('token_contact', $html);

        $response = self::request('POST', '/contact', [
                'name'          => 'error',
                'email'         => 'test@example.com',
                'object'        => 'Object test',
                'message'       => 'Message test',
                'copy'          => 'on',
                'token_contact' => $token
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [ 'content-type' => [ 'application/json' ] ],
            $response->getHeaders()
        );
        $this->assertEquals(
            [
                'messages' => [
                    'errors' => [ 'Une erreur a empêché votre email d\'être envoyé.' ]
                ],
            ],
            json_decode((string) $response->getBody(), true)
        );
    }
}
