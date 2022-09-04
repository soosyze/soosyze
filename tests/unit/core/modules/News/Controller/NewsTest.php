<?php

namespace tests\unit\core\modules\News\Controller;

use Soosyze\Core\Modules\Template\Services\Templating;
use tests\unit\WebTestCase;

class NewsTest extends WebTestCase
{
    /**
     * @dataProvider getNewsProviders
     */
    public function testGetNews(string $url): void
    {
        $response = self::request('GET', $url);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Templating::class, $response);

        $html = (string) $response;
        $this->assertStringContainsString(
            '<title>Articles | Soosyze site</title>',
            $html
        );
    }

    public function getNewsProviders(): \Generator
    {
        yield [ '/news' ];
        yield [ '/news/page/1' ];
    }

    /**
     * @dataProvider getNewsDateProviders
     */
    public function testGetNewsDate(
        string $url,
        string $title,
        string $dateFormat
    ): void {
        $response = self::request('GET', $url);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Templating::class, $response);

        $html = (string) $response;
        $this->assertStringContainsString(
            sprintf($title, t_date($dateFormat, time())),
            $html
        );
    }

    public function getNewsDateProviders(): \Generator
    {
        $year = date('Y');
        yield [
            '/news/' . $year,
            '<title>Articles de %s | Soosyze site</title>',
            'Y',
        ];
        yield [
            '/news/' . $year . '/page/1',
            '<title>Articles de %s | Soosyze site</title>',
            'Y',
        ];

        $month = date('m');
        yield [
            '/news/' . $year . '/' . $month,
            '<title>Articles de %s | Soosyze site</title>',
            'F Y',
        ];
        yield [
            '/news/' . $year . '/' . $month . '/page/1',
            '<title>Articles de %s | Soosyze site</title>',
            'F Y',
        ];

        $day = date('d');
        yield [
            '/news/' . $year . '/' . $month . '/' . $day,
            '<title>Articles de %s | Soosyze site</title>',
            'd F Y',
        ];
        yield [
            '/news/' . $year . '/' . $month . '/' . $day . '/page/1',
            '<title>Articles de %s | Soosyze site</title>',
            'd F Y',
        ];
    }

    public function testGetRss(): void
    {
        $response = self::request('GET', '/news/feed/rss');

        $rss = new \SimpleXMLElement((string) $response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            'Soosyze site',
            $rss->channel->title
        );
        $this->assertEquals(
            'http://localhost',
            $rss->channel->link
        );
        $this->assertEquals(
            'Site powered by Soosyze',
            $rss->channel->description
        );
        $this->assertEquals(
            'fr',
            $rss->channel->language
        );
        $this->assertEquals(
            'Soosyze CMS',
            $rss->channel->generator
        );
    }
}
