<?php

namespace SoosyzeCore\News\Controller;

class NewsController extends \Soosyze\Controller
{
    public static $limit = 10;

    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routing.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function index()
    {
        $nodes = self::query()
            ->from('node')
            ->where('published', '==', 1)
            ->where('type', 'article')
            ->orderBy('created', 'desc')
            ->limit(self::$limit)
            ->fetchAll();

        if (!$nodes) {
            $default = 'Aucun articles pour le moment !';
        } else {
            $default = '';
            foreach ($nodes as &$node) {
                $node[ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':id' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', $this->pathViews, [
                    'nodes'   => $nodes,
                    'default' => $default
        ]);
    }

    public function page($page, $req)
    {
        $offset = self::$limit * $page;

        $nodes = self::query()
            ->from('node')
            ->where('published', '==', 1)
            ->where('type', 'article')
            ->orderBy('created', 'desc')
            ->limit(self::$limit, $offset)
            ->fetchAll();

        if (!$nodes || $page == 0) {
            return $this->get404($req);
        }

        foreach ($nodes as &$node) {
            $node[ 'link_view' ] = self::router()->getRoute('node.show', [
                ':id' => $node[ 'id' ] ]);
        }

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', $this->pathViews, [
                    'nodes'   => $nodes,
                    'default' => ''
        ]);
    }

    public function viewYears($years)
    {
        $nodes = $this->getNews(strtotime('01/01/' . $years), strtotime('01/01/' . $years . ' +1 year'));

        if (!$nodes) {
            $default = 'Aucun articles pour l\'année';
        } else {
            $default = '';
            foreach ($nodes as &$node) {
                $node[ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':id' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Articles de ' . $years
                ])
                ->render('page.content', 'views-news-index.php', $this->pathViews, [
                    'nodes'   => $nodes,
                    'default' => $default
        ]);
    }

    public function viewMonth($years, $month)
    {
        $date        = $month . '/01/' . $years;
        $dateCurrent = strtotime($date);
        $dateNext    = strtotime($date . ' +1 month');

        $nodes = $this->getNews($dateCurrent, $dateNext);

        if (!$nodes) {
            $default = 'Aucun articles pour le mois.';
        } else {
            $default = '';
            foreach ($nodes as &$node) {
                $node[ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':id' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Articles de ' . date('M Y', $dateCurrent)
                ])
                ->render('page.content', 'views-news-index.php', $this->pathViews, [
                    'nodes'   => $nodes,
                    'default' => $default
        ]);
    }

    public function viewDay($years, $month, $day)
    {
        $date        = $month . '/' . $day . '/' . $years;
        $dateCurrent = strtotime($date);
        $dateNext    = strtotime($date . ' +1 day');

        $nodes = $this->getNews($dateCurrent, $dateNext);

        if (!$nodes) {
            $default = 'Aucun articles pour le jour.';
        } else {
            $default = '';
            foreach ($nodes as &$node) {
                $node[ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':id' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->getTheme('theme')
                ->view('page', [
                    'title_main' => 'Articles du ' . date('d M Y', $dateCurrent)
                ])
                ->render('page.content', 'views-news-index.php', $this->pathViews, [
                    'nodes'   => $nodes,
                    'default' => $default
        ]);
    }

    public function viewRss($req)
    {
        return $this->get404($req);
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
}
