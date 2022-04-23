<?php

namespace tests\unit;

use PHPUnit\Framework\TestCase;
use Soosyze\App;
use Soosyze\Components\Http\ServerRequest;
use Soosyze\Components\Http\Uri;

if (!isset($_SESSION)) {
    $_SESSION = [];
}

require_once dirname(__DIR__) . '/fixtures/Functions.php';

class KernelTestCase extends TestCase
{
    /** @var App */
    protected static $app;

    public static function setUpBeforeClass(): void
    {
        $serverRequest = new ServerRequest(
            'GET',
            Uri::create('http://localhost/'),
            [],
            null,
            '1.1',
            [ 'SCRIPT_FILENAME' => '/index.php', 'SCRIPT_NAME' => '/index.php' ]
        );

        self::$app = \Core::getInstance($serverRequest);
        self::$app->setSettings([
            'root'                => ROOT,
            /* Chemin des fichiers de configurations. */
            'config'              => 'app/config',
            /* Chemin des fichiers public. */
            'files_public'        => 'public/files',
            /* Chemin des ressources public. */
            'assets_public'       => 'public/vendor',
            /* Chemin des modules du core. */
            'modules'             => 'core/modules',
            /* Chemin des modules contributeur. */
            'modules_contributed' => 'app/modules',
            /* Chemins des thèmes par ordre de priorité d'appel. */
            'themes_path'         => [ 'app/themes', 'core/themes' ],
            /* Chemin des backups, absolu */
            'backup_dir'          => 'fixtures/soosyze_backups',
            /* Chemin du répertoire utilisé pour les fichiers temporaires. */
            'tmp_dir'             => sys_get_temp_dir()
        ]);

        self::$app->setEnvironmentDefault('test');
        self::$app->init();

        require_once ROOT . 'bootstrap/facade.php';
    }

    protected static function bootKernel(ServerRequest $serverRequest): App
    {
        return self::$app->setRequest(self::mergeServerRequest($serverRequest));
    }

    private static function mergeServerRequest(ServerRequest $serverRequest): ServerRequest
    {
        $request = new ServerRequest(
            $serverRequest->getMethod(),
            $serverRequest->getUri(),
            $serverRequest->getHeaders(),
            $serverRequest->getBody(),
            $serverRequest->getProtocolVersion(),
            array_merge(
                [
                    'SCRIPT_FILENAME' => '/index.php',
                    'SCRIPT_NAME'     => '/index.php'
                ],
                $serverRequest->getServerParams()
            )
        );

        return $request
                ->withParsedBody($serverRequest->getParsedBody())
                ->withQueryParams($serverRequest->getQueryParams());
    }
}
