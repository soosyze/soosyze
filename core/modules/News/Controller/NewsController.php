<?php

namespace SoosyzeCore\News\Controller;

use Soosyze\Components\Paginate\Paginator;

class NewsController extends \Soosyze\Controller
{
    public static $limit = 6;

    protected $dateCurrent;

    protected $dateNext;

    protected $title_main;

    protected $link;

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function index($req)
    {
        return $this->page(1, $req);
    }

    public function page($page, $req)
    {
        $offset = self::$limit * ($page - 1);

        $query = self::query()
            ->from('node')
            ->where('published', '==', 1)
            ->where('type', 'article')
            ->orderBy('created', 'desc')
            ->limit(self::$limit, $offset)
            ->fetchAll();

        $default = '';
        if ($page !== 1 && !$query) {
            return $this->get404($req);
        }
        if (!$query) {
            $default = 'Aucun articles pour le moment !';
        }
        foreach ($query as &$new) {
            $new[ 'link_view' ] = self::router()->getRoute('node.show', [
                ':id' => $new[ 'id' ] ]);
            $new[ 'field' ]     = unserialize($new[ 'field' ]);
        }
        $query_all = self::query()
            ->from('node')
            ->where('published', '==', 1)
            ->where('type', 'article')
            ->fetchAll();

        $link     = self::router()->getRoute('news.page', [], false);
        $paginate = new Paginator(count($query_all), self::$limit, $page, $link);

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', $this->pathViews, [
                    'news'     => $query,
                    'default'  => $default,
                    'paginate' => $paginate,
                    'link_rss' => self::router()->getRoute('news.rss')
        ]);
    }

    public function viewYears($years, $req)
    {
        return $this->viewYearsPage($years, 1, $req);
    }

    public function viewYearsPage($years, $page, $req)
    {
        $date              = '01/01/' . $years;
        $this->dateCurrent = strtotime($date);
        $this->dateNext    = strtotime($date . ' +1 year -1 seconds');
        $this->title_main  = 'Articles de ' . $years;
        $this->link        = self::router()->getRoute('news.page.years', [ ':years' => $years ], false);

        return $this->renderNews($page, $req);
    }

    public function viewMonth($years, $month, $req)
    {
        return $this->viewMonthPage($years, $month, 1, $req);
    }

    public function viewMonthPage($years, $month, $page, $req)
    {
        $date              = $month . '/01/' . $years;
        $this->dateCurrent = strtotime($date);
        $this->dateNext    = strtotime($date . ' +1 month -1 seconds');
        $this->title_main  = 'Articles de ' . date('M Y', $this->dateCurrent);
        $this->link        = self::router()->getRoute('news.page.month', [
            ':years' => $years,
            ':month' => $month
            ], false);

        return $this->renderNews($page, $req);
    }

    public function viewDay($years, $month, $day, $req)
    {
        return $this->viewDayPage($years, $month, $day, 1, $req);
    }

    public function viewDayPage($years, $month, $day, $page, $req)
    {
        $date              = $month . '/' . $day . '/' . $years;
        $this->dateCurrent = strtotime($date);
        $this->dateNext    = strtotime($date . ' +1 day -1 seconds');
        $this->title_main  = 'Articles du ' . date('d M Y', $this->dateCurrent);
        $this->link        = self::router()->getRoute('news.page.day', [
            ':years' => $years,
            ':month' => $month,
            ':day'   => $day
            ], false);

        return $this->renderNews($page, $req);
    }

    public function viewRss($req)
    {
        $query = self::query()
            ->from('node')
            ->where('published', '==', 1)
            ->where('type', 'article')
            ->orderBy('created', 'desc')
            ->limit(self::$limit)
            ->fetchAll();
        $xml   = '<?xml version="1.0" encoding="iso-8859-1"?><rss version="2.0">' . PHP_EOL
            . '<channel>' . PHP_EOL
            . '<title>News</title>' . PHP_EOL
            . '<link>'
            . self::router()->getRoute('news.rss')
            . '</link>' . PHP_EOL
            . '<description></description>' . PHP_EOL
            . '<language>fr</language>' . PHP_EOL;
        foreach ($query as $new) {
            $new[ 'field' ] = unserialize($new[ 'field' ]);
            $xml            .= '<item>' . PHP_EOL
                . '<title>' . htmlspecialchars($new[ 'title' ]) . '</title>' . PHP_EOL
                . '<link>'
                . self::router()->getRoute('node.show', [ ':id' => $new[ 'id' ] ])
                . '</link>' . PHP_EOL
                . '<pubDate>' . date('D, d M Y H:i:s O', $new[ 'created' ]) . '</pubDate>' . PHP_EOL
                . '<description>' . htmlspecialchars($new[ 'field' ][ 'summary' ]) . '</description>' . PHP_EOL
                . '</item>' . PHP_EOL;
        }
        $xml .= '</channel>' . PHP_EOL . '</rss>' . PHP_EOL;

        $stream = new \Soosyze\Components\Http\Stream($xml);

        return (new \Soosyze\Components\Http\Response(200, $stream))
                ->withHeader('content-Type', 'application/rss+xml; charset=utf-8')
                ->withHeader('content-length', $stream->getSize())
                ->withHeader('content-disposition', 'attachment; filename=rss.xml');
    }

    protected function renderNews($page, $req)
    {
        $offset = self::$limit * ($page - 1);
        $news   = $this->getNews($this->dateCurrent, $this->dateNext, $offset);

        if (!$news) {
            return $this->get404($req);
        }
        foreach ($news as &$new) {
            $new[ 'link_view' ] = self::router()->getRoute('node.show', [
                ':id' => $new[ 'id' ] ]);
            $new[ 'field' ]     = unserialize($new[ 'field' ]);
        }

        $nodes_all = $this->getNewsAll($this->dateCurrent, $this->dateNext);
        $paginate  = new Paginator(count($nodes_all), self::$limit, $page, $this->link);

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => $this->title_main
                ])
                ->render('page.content', 'views-news-index.php', $this->pathViews, [
                    'news'     => $news,
                    'paginate' => $paginate,
                    'link_rss' => self::router()->getRoute('news.rss')
        ]);
    }

    protected function getNews($dateCurrent, $dateNext, $offset = 0)
    {
        return self::query()
                ->from('node')
                ->where('type', 'article')
                ->between('created', $dateCurrent, $dateNext)
                ->where('published', '==', 1)
                ->orderBy('created', 'desc')
                ->limit(self::$limit, $offset)
                ->fetchAll();
    }

    protected function getNewsAll($dateCurrent, $dateNext)
    {
        return self::query()
                ->from('node')
                ->where('type', 'article')
                ->between('created', $dateCurrent, $dateNext)
                ->where('published', '==', 1)
                ->fetchAll();
    }
}
