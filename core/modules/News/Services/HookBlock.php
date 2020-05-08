<?php

namespace SoosyzeCore\News\Services;

class HookBlock
{
    /**
     * @var \Queryflatfile\Request
     */
    protected $query;

    protected $router;

    public function __construct($query, $router)
    {
        $this->query     = $query;
        $this->router    = $router;
        $this->pathViews = dirname(__DIR__) . '/Views/';
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
}
