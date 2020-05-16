<?php

namespace SoosyzeCore\News\Controller;

use Soosyze\Components\Paginate\Paginator;

class News extends \Soosyze\Controller
{
    public static $limit;

    protected $dateCurrent;

    protected $dateNext;

    protected $titleMain;

    protected $link;

    protected $pathViews;

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function index($req)
    {
        return $this->page(1, $req);
    }

    public function page($page, $req)
    {
        self::$limit = self::config()->get('settings.news_pagination', 6);

        $query = self::query()
            ->from('node')
            ->where('node_status_id', 1)
            ->where('type', 'article')
            ->orderBy('date_created', 'desc')
            ->limit(self::$limit, self::$limit * ($page - 1))
            ->fetchAll();

        if ($page !== 1 && !$query) {
            return $this->get404($req);
        }

        $default = $query
            ? ''
            : t('No articles for the moment');

        foreach ($query as &$value) {
            $value[ 'field' ] = self::node()->makeFieldsById('article', $value[ 'entity_id' ]);

            if ($alias = self::alias()->getAlias('node/' . $value[ 'id' ])) {
                $value[ 'link_view' ] = self::router()->makeRoute($alias);
            } else {
                $value[ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':id_node' => $value[ 'id' ]
                ]);
            }
        }
        $queryAll = self::query()
            ->from('node')
            ->where('node_status_id', 1)
            ->where('type', 'article')
            ->fetchAll();

        $link = self::router()->getRoute('news.page', [], false);

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->make('page.content', 'views-news-index.php', $this->pathViews, [
                    'news'     => $query,
                    'default'  => $default,
                    'paginate' => new Paginator(count($queryAll), self::$limit, $page, $link),
                    'link_rss' => self::router()->getRoute('news.rss')
        ]);
    }

    public function viewYears($years, $page, $req)
    {
        $date              = '01/01/' . $years;
        $this->dateCurrent = strtotime($date);
        $this->dateNext    = strtotime($date . ' +1 year -1 seconds');
        $this->titleMain   = t('Articles from :date', [ ':date' => $years ]);
        $this->link        = self::router()->getRoute('news.years', [ ':year' => $years ], false);

        return $this->renderNews($page, $req);
    }

    public function viewMonth($years, $month, $page, $req)
    {
        $date              = $month . '/01/' . $years;
        $this->dateCurrent = strtotime($date);
        $this->dateNext    = strtotime($date . ' +1 month -1 seconds');
        $this->titleMain   = t('Articles from :date', [ ':date' => date('M Y', $this->dateCurrent) ]);
        $this->link        = self::router()->getRoute('news.month', [
            ':year'  => $years,
            ':month' => $month
            ], false);

        return $this->renderNews($page, $req);
    }

    public function viewDay($years, $month, $day, $page, $req)
    {
        $date              = $month . '/' . $day . '/' . $years;
        $this->dateCurrent = strtotime($date);
        $this->dateNext    = strtotime($date . ' +1 day -1 seconds');
        $this->titleMain   = t('Articles from :date', [ ':date' => date('d M Y', $this->dateCurrent) ]);
        $this->link        = self::router()->getRoute('news.day', [
            ':year'  => $years,
            ':month' => $month,
            ':day'   => $day
            ], false);

        return $this->renderNews($page, $req);
    }

    public function viewRss($req)
    {
        self::$limit = self::config()->get('settings.news_pagination', 6);

        $query = self::query()
            ->from('node')
            ->where('node_status_id', 1)
            ->where('type', 'article')
            ->orderBy('date_created', 'desc')
            ->limit(self::$limit)
            ->fetchAll();

        foreach ($query as &$new) {
            $new[ 'field' ]      = self::node()->makeFieldsById('article', $new[ 'entity_id' ]);
            $new[ 'route_show' ] = self::router()->getRoute('node.show', [ ':id_node' => $new[ 'id' ] ]);
        }

        $stream = new \Soosyze\Components\Http\Stream(
            self::template()
                ->createBlock('page-rss.php', $this->pathViews)
                ->addVars([
                    'routeRss' => self::router()->getRoute('news.rss'),
                    'news'     => $query
                ])
        );

        return (new \Soosyze\Components\Http\Response(200, $stream))
                ->withHeader('content-Type', 'application/rss+xml; charset=utf-8')
                ->withHeader('content-length', $stream->getSize())
                ->withHeader('content-disposition', 'attachment; filename=rss.xml');
    }

    protected function renderNews($page, $req)
    {
        $page = !empty($page)
            ? substr(strrchr($page, '/'), 1)
            : 1;

        self::$limit = self::config()->get('settings.news_pagination', 6);
        $offset      = self::$limit * ($page - 1);
        $news        = $this->getNews($this->dateCurrent, $this->dateNext, $offset);

        $isCurrent = (time() >= $this->dateCurrent && time() <= $this->dateNext);

        $default = '';
        if (!$news) {
            if ($page == 1 && $isCurrent) {
                $default = t('No articles for the moment');
            } else {
                return $this->get404($req);
            }
        }

        foreach ($news as &$new) {
            $new[ 'link_view' ] = self::router()->getRoute('node.show', [
                ':id_node' => $new[ 'id' ]
            ]);
            $new[ 'field' ]     = self::node()->makeFieldsById('article', $new[ 'entity_id' ]);
        }

        $nodesAll = $this->getNewsAll($this->dateCurrent, $this->dateNext);

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => $this->titleMain
                ])
                ->make('page.content', 'views-news-index.php', $this->pathViews, [
                    'news'     => $news,
                    'paginate' => new Paginator(count($nodesAll), self::$limit, $page, $this->link),
                    'default'  => $default,
                    'link_rss' => self::router()->getRoute('news.rss')
        ]);
    }

    protected function getNews($dateCurrent, $dateNext, $offset = 0)
    {
        return self::query()
                ->from('node')
                ->where('type', 'article')
                ->between('date_created', $dateCurrent, $dateNext)
                ->where('node_status_id', 1)
                ->orderBy('date_created', 'desc')
                ->limit(self::$limit, $offset)
                ->fetchAll();
    }

    protected function getNewsAll($dateCurrent, $dateNext)
    {
        return self::query()
                ->from('node')
                ->where('type', 'article')
                ->between('date_created', $dateCurrent, $dateNext)
                ->where('node_status_id', 1)
                ->fetchAll();
    }
}
