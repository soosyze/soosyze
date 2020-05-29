<?php

namespace SoosyzeCore\News\Services;

class HookBlock
{
    protected $alias;

    protected $node;

    protected $pathViews;
    /**
     * @var \Queryflatfile\Request
     */
    protected $query;

    protected $router;

    public function __construct($alias, $node, $query, $router)
    {
        $this->alias     = $alias;
        $this->node      = $node;
        $this->pathViews = dirname(__DIR__) . '/Views/';
        $this->query     = $query;
        $this->router    = $router;
    }

    public function hookNewShow(array &$blocks)
    {
        $blocks[ 'news.year' ]  = [
            'title'     => t('Archives by years'),
            'tpl'       => 'block-news-year.php',
            'path'      => $this->pathViews,
            'key_block' => 'news.year',
            'hook'      => 'news.year'
        ];
        $blocks[ 'news.month' ] = [
            'title'     => t('Archives by months'),
            'tpl'       => 'block-news-month.php',
            'path'      => $this->pathViews,
            'key_block' => 'news.month',
            'hook'      => 'news.month'
        ];
        $blocks[ 'news.last' ]  = [
            'title'     => t('Last News'),
            'tpl'       => 'block-news-last.php',
            'path'      => $this->pathViews,
            'key_block' => 'news.last',
            'hook'      => 'news.last'
        ];
    }

    public function hookBlockNewsYear($tpl)
    {
        $data = $this->query
            ->from('node')
            ->where('node_status_id', '==', 1)
            ->where('type', 'article')
            ->fetchAll();

        $output = [];
        foreach ($data as $value) {
            $year = date('Y', $value[ 'date_created' ]);
            if (isset($output[ $year ])) {
                $output[ $year ][ 'number' ]++;

                continue;
            }
            $output[ $year ] = [
                'number' => 1,
                'year'   => $year,
                'link'   => $this->router->getRoute('news.years', [
                    ':year' => $year,
                    ':id'   => ''
                ])
            ];
        }

        return $tpl->addVar('archive', $output);
    }

    public function hookBlockNewsMonth($tpl)
    {
        $data = $this->query
            ->from('node')
            ->where('node_status_id', '==', 1)
            ->where('type', 'article')
            ->fetchAll();

        $output = [];
        foreach ($data as $value) {
            $year  = date('Y', $value[ 'date_created' ]);
            $month = date('m', $value[ 'date_created' ]);

            if (!isset($output[ $year ])) {
                $output[ $year ] = [
                    'number' => 1,
                    'year'   => $year,
                    'link'   => $this->router->getRoute('news.years', [
                        ':year' => $year,
                        ':id'   => ''
                    ])
                ];
            }

            if (!isset($output[ $year ][ 'months' ][ $month ])) {
                $output[ $year ][ 'months' ][ $month ] = [
                    'number' => 1,
                    'year'   => $year,
                    'month'  => date('M', $value[ 'date_created' ]),
                    'link'   => $this->router->getRoute('news.month', [
                        ':year'  => $year,
                        ':month' => $month,
                        ':id'    => ''
                    ])
                ];
            } else {
                $output[ $year ][ 'number' ]++;
                $output[ $year ][ 'months' ][ $month ][ 'number' ]++;
            }
        }

        return $tpl->addVar('years', $output);
    }
    
    public function hookBlockNewsLast($tpl)
    {
        $news = $this->query
            ->from('node')
            ->where('node_status_id', 1)
            ->where('type', 'article')
            ->orderBy('date_created', 'desc')
            ->limit(4)
            ->fetchAll();

        $link_news = false;
        foreach ($news as $key => &$value) {
            if ($key > 2) {
                $link_news = $this->router->getRoute('news.index');
                unset($news[ $key ]);

                continue;
            }
            $value[ 'field' ] = $this->node->makeFieldsById('article', $value[ 'entity_id' ]);

            if ($alias = $this->alias->getAlias('node/' . $value[ 'id' ])) {
                $value[ 'link_view' ] = $this->router->makeRoute($alias);
            } else {
                $value[ 'link_view' ] = $this->router->getRoute('node.show', [
                    ':id_node' => $value[ 'id' ]
                ]);
            }
        }

        return $tpl->addVars([
                'default'   => t('No articles for the moment'),
                'news'      => $news,
                'link_news' => $link_news
        ]);
    }
}
