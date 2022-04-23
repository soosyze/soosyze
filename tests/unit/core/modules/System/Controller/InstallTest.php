<?php

namespace tests\unit\core\modules\System\Controller;

use tests\unit\WebTestCase;

class InstallTest extends WebTestCase
{
    private const DATA_DIR      = ROOT . '/app/data/test';

    private const SETTINGS_FILE = ROOT . '/app/config/test/settings.json';

    public static function setUpBeforeClass(): void
    {
        $dir = new \DirectoryIterator(self::DATA_DIR);
        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->getRealPath() === false) {
                continue;
            }
            if ($fileInfo->getExtension() === 'md') {
                continue;
            }
            unlink($fileInfo->getRealPath());
        }
        if (file_exists(self::SETTINGS_FILE)) {
            unlink(self::SETTINGS_FILE);
        }

        parent::setUpBeforeClass();
    }

    public function testGetRedirectToInstallStepOne(): void
    {
        $response = self::request('GET', '/');

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(
            [ 'location' => [ 'http://localhost/install' ] ],
            $response->getHeaders()
        );
    }

    public function testGetInstallStepChooseLanguage(): void
    {
        $response = self::request('GET', '/install');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            '<title>Soosyze | Choose language</title>',
            (string) $response->getBody()
        );
    }

    public function testPostInstallStepChooseLanguageError(): void
    {
        $response = self::request('POST', '/install/step/language', [
                'lang'     => 'error',
                'timezone' => 'error'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(
            [ 'location' => [ 'http://localhost/install/step/language' ] ],
            $response->getHeaders()
        );
    }

    public function testPostInstallStepChooseLanguage(): void
    {
        $response = self::request('POST', '/install/step/language', [
                'lang'     => 'fr',
                'timezone' => 'Europe/London'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(
            [ 'location' => [ 'http://localhost/install/step/profil' ] ],
            $response->getHeaders()
        );
    }

    public function testGetInstallStepProfil(): void
    {
        $response = self::request('GET', '/install/step/profil');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            '<title>Soosyze | Installation profile</title>',
            (string) $response->getBody()
        );
    }

    public function testPostInstallStepProfilRedirectToError(): void
    {
        $response = self::request('POST', '/install/step/profil', [
                'profil'             => 'error',
                'token_step_install' => 'error'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(
            [ 'location' => [ 'http://localhost/install/step/profil' ] ],
            $response->getHeaders()
        );
    }

    public function testPostInstallStepProfil(): void
    {
        $html  = self::request('GET', '/install/step/profil');
        $token = $this->getToken('token_step_install', (string) $html->getBody());

        $response = self::request('POST', '/install/step/profil', [
                'profil'             => 'site',
                'token_step_install' => $token,
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(
            [ 'location' => [ 'http://localhost/install/step/user' ] ],
            $response->getHeaders()
        );
    }

    public function testGetInstallStepUser(): void
    {
        $response = self::request('GET', '/install/step/user');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            '<title>Soosyze | User profile</title>',
            (string) $response->getBody()
        );
    }

    public function testPostInstallStepUser(): void
    {
        $_SESSION[ 'lang' ]                 = [ 'fr' ];
        $_SESSION[ 'inputs' ][ 'language' ] = [
            'lang'     => 'fr',
            'timezone' => 'Europe/London'
        ];

        $_SESSION[ 'inputs' ][ 'profil' ] = [
            'profil' => 'site'
        ];
        $_SESSION[ 'inputs' ][ 'user' ]   = [
            'username'         => 'LordSnow',
            'email'            => 'email@exemple.com',
            'name'             => 'Snow',
            'firstname'        => 'Jon',
            'password'         => '123456789',
            'password_confirm' => '123456789',
        ];

        $response = self::request('POST', '/install/step/user', $_SESSION[ 'inputs' ][ 'user' ]);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(
            [ 'location' => [ 'http://localhost' ] ],
            $response->getHeaders()
        );
    }
}
