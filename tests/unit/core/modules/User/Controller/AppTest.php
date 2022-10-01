<?php

namespace tests\unit\core\modules\User\Controller;

use Psr\Http\Message\ResponseInterface;
use Soosyze\Core\Modules\Template\Services\Templating;
use tests\unit\WebTestCase;

class AppTest extends WebTestCase
{
    /**
     * @dataProvider getNewsDateProviders
     */
    public function testHomePage(
        string $url,
        int $code
    ): void {
        $response = self::request('GET', $url);

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertInstanceOf(Templating::class, $response);
    }

    public function getNewsDateProviders(): \Generator
    {
        yield [
            '/', 200,
        ];
        yield [
            '/admin/modules', 403,
        ];
        yield [
            '/path/to/404', 404,
        ];
    }
}
