<?php

namespace tests\unit;

use Psr\Http\Message\ResponseInterface;
use Soosyze\Components\Http\ServerRequest;
use Soosyze\Components\Http\Stream;
use Soosyze\Components\Http\Uri;

class WebTestCase extends KernelTestCase
{
    /**
     * @param mixed $content
     */
    public static function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $uploadFiles = [],
        array $serverParams = [],
        $content = null
    ): ResponseInterface {
        $serverRequest = (new ServerRequest(
            $method,
            Uri::create('http://localhost' . $uri),
            [],
            new Stream($content),
            '1.1',
            $serverParams,
            [],
            $uploadFiles
        ))
            ->withParsedBody($parameters)
        ;

        return self::bootKernel($serverRequest)->run();
    }

    public static function createClient(ServerRequest $serverRequest): ResponseInterface
    {
        return self::bootKernel($serverRequest)->run();
    }

    public function getToken(string $name, string $content): ?string
    {
        preg_match('<input name="' . $name . '" type="hidden" value="(?P<token>.*)">', $content, $matches);

        return $matches[ 'token' ]
            ?? null;
    }
}
