<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\News\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Http\Response;
use Soosyze\Components\Http\Stream;
use Soosyze\Components\Paginate\Paginator;
use Soosyze\Core\Modules\News\Hook\Config;

/**
 * @method \Soosyze\Core\Modules\System\Services\Alias        alias()
 * @method \Soosyze\Core\Modules\Node\Services\Node           node()
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query  query()
 * @method \Soosyze\Core\Modules\Template\Services\Templating template()
 *
 * @phpstan-import-type NodeEntity from \Soosyze\Core\Modules\Node\Extend
 */
class News extends \Soosyze\Controller
{
    /**
     * @var int
     */
    private $dateCurrent;

    /**
     * @var int
     */
    private $dateNext;

    /**
     * @var string
     */
    private $titleMain;

    /**
     * @var string
     */
    private $link;

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function page(ServerRequestInterface $req, int $pageId = 1): ResponseInterface
    {
        /** @phpstan-var int $limit */
        $limit = self::config()->get('settings.news_pagination', Config::PAGINATION);

        /** @phpstan-var array<NodeEntity> $query */
        $query = self::query()
            ->from('node')
            ->where('node_status_id', '=', 1)
            ->where('type', '=', 'article')
            ->orderBy('sticky', SORT_DESC)
            ->orderBy('date_created', SORT_DESC)
            ->limit($limit, $limit * ($pageId - 1))
            ->fetchAll();

        if ($pageId !== 1 && !$query) {
            return $this->get404($req);
        }

        $default = $query === []
            ? t('No articles for the moment')
            : '';

        foreach ($query as &$value) {
            $value[ 'field' ] = self::node()->makeFieldsById('article', $value[ 'entity_id' ]);

            /** @phpstan-var string $alias */
            $alias = self::alias()->getAlias('node/' . $value[ 'id' ], 'node/' . $value[ 'id' ]);

            $value[ 'link_view' ] = self::router()->makeUrl('/' . ltrim($alias, '/'));
        }
        unset($value);

        $queryAll = self::query()
            ->from('node')
            ->where('node_status_id', '=', 1)
            ->where('type', '=', 'article')
            ->fetchAll();

        $link = self::router()->generateUrl('news.page', [], false);

        /** @phpstan-var string $titleMain */
        $titleMain = self::config()->get('settings.new_title', Config::TITLE);

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => t($titleMain)
                ])
                ->make('page.content', 'news/content-news-index.php', $this->pathViews, [
                    'default'  => $default,
                    'news'     => $query,
                    'link_rss' => self::router()->generateUrl('news.rss'),
                    'paginate' => (new Paginator(count($queryAll), $limit, $pageId, $link))
                        ->setKey('{pageId}')
        ]);
    }

    public function viewYears(
        string $year,
        ServerRequestInterface $req,
        int $pageId = 1
    ): ResponseInterface {
        $date              = '01/01/' . $year;
        $this->dateCurrent = self::tryStrtotime($date);
        $this->dateNext    = self::tryStrtotime($date . ' +1 year -1 seconds');
        $this->titleMain   = t('Articles from :date', [ ':date' => $year ]);
        $this->link        = self::router()->generateUrl('news.years.page', [ 'year' => $year ], false);

        return $this->renderNews($pageId, $req);
    }

    public function viewMonth(
        string $year,
        string $month,
        ServerRequestInterface $req,
        int $pageId = 1
    ): ResponseInterface {
        $date              = $month . '/01/' . $year;
        $this->dateCurrent = self::tryStrtotime($date);
        $this->dateNext    = self::tryStrtotime($date . ' +1 month -1 seconds');
        $this->titleMain   = t('Articles from :date', [ ':date' => strftime('%B %Y', $this->dateCurrent) ]);
        $this->link        = self::router()->generateUrl('news.month.page', [
            'year'  => $year,
            'month' => $month
            ], false);

        return $this->renderNews($pageId, $req);
    }

    public function viewDay(
        string $year,
        string $month,
        string $day,
        ServerRequestInterface $req,
        int $pageId = 1
    ): ResponseInterface {
        $date              = $month . '/' . $day . '/' . $year;
        $this->dateCurrent = self::tryStrtotime($date);
        $this->dateNext    = self::tryStrtotime($date . ' +1 day -1 seconds');
        $this->titleMain   = t('Articles from :date', [ ':date' => strftime('%d %B %Y', $this->dateCurrent) ]);
        $this->link        = self::router()->generateUrl('news.day.page', [
            'year'  => $year,
            'month' => $month,
            'day'   => $day
            ], false);

        return $this->renderNews($pageId, $req);
    }

    public function viewRss(ServerRequestInterface $req): ResponseInterface
    {
        /** @phpstan-var int $limit */
        $limit = self::config()->get('settings.news_pagination', Config::PAGINATION);

        /** @phpstan-var array<NodeEntity> $items */
        $items = self::query()
            ->from('node')
            ->where('node_status_id', '=', 1)
            ->where('type', '=', 'article')
            ->orderBy('date_created', SORT_DESC)
            ->limit($limit)
            ->fetchAll();

        foreach ($items as &$item) {
            $item[ 'field' ] = self::node()->makeFieldsById('article', $item[ 'entity_id' ]);

            /** @phpstan-var string $alias */
            $alias = self::alias()->getAlias('node/' . $item[ 'id' ], 'node/' . $item[ 'id' ]);

            $item[ 'link' ] = self::router()->makeUrl('/' . ltrim($alias, '/'));
        }
        unset($item);

        $lastBuildDate = $items[ 0 ][ 'date_created' ] ?? '';

        $stream = new Stream(
            self::template()
                ->createBlock('news/page-news-rss.php', $this->pathViews)
                ->addVars([
                    'description'   => self::config()->get('settings.meta_description', ''),
                    'items'         => $items,
                    'language'      => self::config()->get('settings.lang', 'en'),
                    'lastBuildDate' => $lastBuildDate,
                    'link'          => self::router()->getBasePath(),
                    'title'         => self::config()->get('settings.meta_title', ''),
                    'xml'           => '<?xml version="1.0" encoding="UTF-8" ?>'
                ])
        );

        return (new Response(200, $stream))
                ->withHeader('content-type', 'application/octet-stream')
                ->withHeader('content-length', (string) $stream->getSize())
                ->withHeader('content-disposition', 'attachment; filename=rss.xml')
                ->withHeader('pragma', 'no-cache')
                ->withHeader('cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->withHeader('expires', '0');
    }

    private function renderNews(int $pageId, ServerRequestInterface $req): ResponseInterface
    {
        /** @phpstan-var int $limit */
        $limit = self::config()->get('settings.news_pagination', Config::PAGINATION);

        $offset = $limit * ($pageId - 1);
        $news   = $this->getNews($this->dateCurrent, $this->dateNext, $limit, $offset);

        $isCurrent = (time() >= $this->dateCurrent && time() <= $this->dateNext);

        $default = t('No articles for the moment');
        if (!$news && !($pageId == 1 && $isCurrent)) {
            return $this->get404($req);
        }

        foreach ($news as &$new) {
            $new[ 'field' ] = self::node()->makeFieldsById('article', $new[ 'entity_id' ]);

            /** @phpstan-var string $alias */
            $alias = self::alias()->getAlias('node/' . $new[ 'id' ], 'node/' . $new[ 'id' ]);

            $new[ 'link_view' ] = self::router()->makeUrl('/' . ltrim($alias, '/'));
        }
        unset($new);

        $nodesAll = $this->getNewsAll($this->dateCurrent, $this->dateNext);

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => $this->titleMain
                ])
                ->make('page.content', 'news/content-news-index.php', $this->pathViews, [
                    'news'     => $news,
                    'paginate' => (new Paginator(count($nodesAll), $limit, $pageId, $this->link))
                        ->setKey('{pageId}'),
                    'default'  => $default,
                    'link_rss' => self::router()->generateUrl('news.rss')
        ]);
    }

    private function getNews(
        int $dateCurrent,
        int $dateNext,
        int $limit,
        int $offset = 0
    ): array {
        return self::query()
                ->from('node')
                ->where('type', '=', 'article')
                ->between('date_created', (string) $dateCurrent, (string) $dateNext)
                ->where('node_status_id', '=', 1)
                ->orderBy('sticky', SORT_DESC)
                ->orderBy('date_created', SORT_DESC)
                ->limit($limit, $offset)
                ->fetchAll();
    }

    private function getNewsAll(int $dateCurrent, int $dateNext): array
    {
        return self::query()
                ->from('node')
                ->where('type', '=', 'article')
                ->between('date_created', (string) $dateCurrent, (string) $dateNext)
                ->where('node_status_id', '=', 1)
                ->fetchAll();
    }

    private static function tryStrtotime(string $datetime): int
    {
        $time = strtotime($datetime);
        if ($time === false) {
            throw new \InvalidArgumentException('The date must be in valid format.');
        }

        return $time;
    }
}
