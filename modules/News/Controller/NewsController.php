<?php

namespace News\Controller;

define("VIEWS_NIEWS", MODULES_CORE . 'News' . DS . 'Views' . DS);
define("CONFIG_NIEWS", MODULES_CORE . 'News' . DS . 'Config' . DS);

class NewsController extends \Soosyze\Controller
{
    public static $limit = 4;

    public function __construct()
    {
        $this->pathServices = CONFIG_NIEWS . 'service.json';
        $this->pathRoutes   = CONFIG_NIEWS . 'routing.json';
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
            $default = "Aucun articles pour le moment !";
        } else {
            $default = "";
            foreach ($nodes as $key => $node) {
                $nodes[ $key ][ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':item' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', VIEWS_NIEWS, [
                    'nodes'   => $nodes,
                    'default' => $default,
                    'router'  => self::router()
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

        foreach ($nodes as $key => $node) {
            $nodes[ $key ][ 'link_view' ] = self::router()->getRoute('node.show', [
                ':item' => $node[ 'id' ] ]);
        }

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', VIEWS_NIEWS, [
                    'nodes'   => $nodes,
                    'default' => '',
                    'router'  => self::router()
        ]);
    }

    public function viewYears($years)
    {
        $nodes = $this->getNews(strtotime('01/01/' . $years), strtotime('01/01/' . $years . ' +1 year'));

        if (!$nodes) {
            $default = "Aucun articles pour l'année";
        } else {
            $default = "";
            foreach ($nodes as $key => $node) {
                $nodes[ $key ][ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':item' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', VIEWS_NIEWS, [
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
            $default = "Aucun articles pour le mois.";
        } else {
            $default = "";
            foreach ($nodes as $key => $node) {
                $nodes[ $key ][ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':item' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', VIEWS_NIEWS, [
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
            $default = "Aucun articles pour le jour.";
        } else {
            $default = "";
            foreach ($nodes as $key => $node) {
                $nodes[ $key ][ 'link_view' ] = self::router()->getRoute('node.show', [
                    ':item' => $node[ 'id' ] ]);
            }
        }

        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => 'Articles'
                ])
                ->render('page.content', 'views-news-index.php', VIEWS_NIEWS, [
                    'nodes'   => $nodes,
                    'default' => $default
        ]);
    }

    protected function getNews($dateCurrent, $dateNext, $offset = 0)
    {
        return self::query()
                ->from('node')
                ->where('type', 'article')
                ->bwetween('created', $dateCurrent, $dateNext)
                ->where('published', '==', 1)
                ->limit(self::$limit, $offset)
                ->fetchAll();
    }
}
