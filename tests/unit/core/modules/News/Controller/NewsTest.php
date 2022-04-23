<?php

namespace tests\unit\core\modules\News\Controller;

use SoosyzeCore\Template\Services\Templating;
use tests\unit\WebTestCase;

class NewsTest extends WebTestCase
{
    /**
     * @dataProvider getNewsProviders
     */
    public function testGetNews(string $url, string $title): void
    {
        /** @phpstan-var Templating $response */
        $response = self::request('GET', $url);

        $this->assertEquals(200, $response->getStatusCode());
        $html = (string) $response;
        $this->assertStringContainsString($title, $html);
    }

    public function getNewsProviders(): \Generator
    {
        yield [
            '/news',
            '<title>Articles | Soosyze site</title>',
        ];
        yield [
            '/news/page/1',
            '<title>Articles | Soosyze site</title>',
        ];
        $year  = date('Y');
        yield [
            '/news/' . $year,
            '<title>Articles de ' . $year . ' | Soosyze site</title>',
        ];
        yield [
            '/news/' . $year . '/page/1',
            '<title>Articles de ' . $year . ' | Soosyze site</title>',
        ];
        $month = date('m');
        $title = strftime('%B %Y');
        yield [
            '/news/' . $year . '/' . $month,
            '<title>Articles de ' . $title . ' | Soosyze site</title>',
        ];
        yield [
            '/news/' . $year . '/' . $month . '/page/1',
            '<title>Articles de ' . $title . ' | Soosyze site</title>',
        ];
        $day   = date('d');
        $title = strftime('%d %B %Y');
        yield [
            '/news/' . $year . '/' . $month . '/' . $day,
            '<title>Articles de ' . $title . ' | Soosyze site</title>',
        ];
        yield [
            '/news/' . $year . '/' . $month . '/' . $day . '/page/1',
            '<title>Articles de ' . $title . ' | Soosyze site</title>',
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
