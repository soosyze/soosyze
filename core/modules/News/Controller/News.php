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
            ->orderBy('sticky', 'desc')
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
        unset($value);

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
                ->make('page.content', 'news/content-news-index.php', $this->pathViews, [
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
        $this->link        = self::router()->getRoute('news.years.page', [ ':year' => $years ], false);

        return $this->renderNews($page, $req);
    }

    public function viewMonth($years, $month, $page, $req)
    {
        $date              = $month . '/01/' . $years;
        $this->dateCurrent = strtotime($date);
        $this->dateNext    = strtotime($date . ' +1 month -1 seconds');
        $this->titleMain   = t('Articles from :date', [ ':date' => strftime('%B %Y', $this->dateCurrent) ]);
        $this->link        = self::router()->getRoute('news.month.page', [
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
        $this->titleMain   = t('Articles from :date', [ ':date' => strftime('%d %B %Y', $this->dateCurrent) ]);
        $this->link        = self::router()->getRoute('news.day.page', [
            ':year'  => $years,
            ':month' => $month,
            ':day'   => $day
            ], false);

        return $this->renderNews($page, $req);
    }

    public function viewRss($req)
    {
        self::$limit = self::config()->get('settings.news_pagination', 6);

        $items = self::query()
            ->from('node')
            ->where('node_status_id', 1)
            ->where('type', 'article')
            ->orderBy('date_created', 'desc')
            ->limit(self::$limit)
            ->fetchAll();

        foreach ($items as &$item) {
            $item[ 'field' ] = self::node()->makeFieldsById('article', $item[ 'entity_id' ]);

            if ($alias = self::alias()->getAlias('node/' . $item[ 'id' ])) {
                $item[ 'link' ] = self::router()->makeRoute($alias);
            } else {
                $item[ 'link' ] = self::router()->getRoute('node.show', [
                    ':id_node' => $item[ 'id' ]
                ]);
            }
        }
        unset($item);
        
        $lastBuildDate = isset($items[0]['date_created'])
            ? $items[0]['date_created']
            : '';

        $stream = new \Soosyze\Components\Http\Stream(
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

        return (new \Soosyze\Components\Http\Response(200, $stream))
                ->withHeader('Content-Type', 'application/rss+xml; charset=utf-8')
                ->withHeader('Content-Length', $stream->getSize())
                ->withHeader('Content-Disposition', 'attachment; filename=rss.xml');
    }

    protected function renderNews($page, $req)
    {
        $page = !empty($page)
            ? substr(strrchr($page, '/'), 1)
            : 1;
        
        self::$limit = self::config()->get('settings.news_pagination', 6);

        $offset = self::$limit * ($page - 1);
        $news   = $this->getNews($this->dateCurrent, $this->dateNext, $offset);

        $isCurrent = (time() >= $this->dateCurrent && time() <= $this->dateNext);

        $default = t('No articles for the moment');
        if (!$news && !($page == 1 && $isCurrent)) {
            return $this->get404($req);
        }

        foreach ($news as &$new) {
            $new[ 'field' ]     = self::node()->makeFieldsById('article', $new[ 'entity_id' ]);
            
            if ($alias = self::alias()->getAlias('node/' . $new[ 'id' ])) {
                $new[ 'link_view' ] = self::router()->makeRoute($alias);
            } else {
                $new[ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':id_node' => $new[ 'id' ]
                ]);
            }
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
                ->orderBy('sticky', 'desc')
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
