<?php

namespace SoosyzeCore\News\Services;

class HookBlock
{
    /**
     * @var \SoosyzeCore\System\Services\Alias
     */
    protected $alias;

    /**
     * @var \SoosyzeCore\Node\Services\Node
     */
    protected $node;

    /**
     * @var string
     */
    protected $pathViews;

    /**
     * @var \Queryflatfile\Request
     */
    protected $query;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    protected $router;

    public function __construct($alias, $node, $query, $router)
    {
        $this->alias  = $alias;
        $this->node   = $node;
        $this->query  = $query;
        $this->router = $router;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function hookNewShow(array &$blocks)
    {
        $blocks[ 'news.archive' ] = [
            'hook'    => 'news.archive',
            'options' => [ 'expand' => false ],
            'path'    => $this->pathViews,
            'title'   => 'Archives',
            'tpl'     => 'components/block/news-archive.php'
        ];
        $blocks[ 'news.last' ]    = [
            'hook'    => 'news.last',
            'options' => [ 'limit' => 3, 'offset' => 0, 'more' => true ],
            'path'    => $this->pathViews,
            'title'   => 'Last News',
            'tpl'     => 'components/block/news-last.php'
        ];
    }

    public function hookBlockNewsArchive($tpl, array $options)
    {
        $data = $this->query
            ->from('node')
            ->where('node_status_id', '==', 1)
            ->where('type', 'article')
            ->fetchAll();
        
        $query = $this->router->parseQueryFromRequest();
        $param = $this->router->parseParam('news/:year:id', $query, [
            ':year' => '\d{4}',
            ':id'   => '(/page/[1-9]\d*)?'
        ]);

        $output = [];
        foreach ($data as $value) {
            $year  = date('Y', $value[ 'date_created' ]);
            $month = date('m', $value[ 'date_created' ]);

            if (isset($output[ $year ])) {
                ++$output[ $year ][ 'number' ];
            } else {
                $output[ $year ] = [
                    'number' => 1,
                    'year'   => $year,
                    'link'   => $this->router->getRoute('news.years', [
                        ':year' => $year,
                        ':id'   => ''
                    ])
                ];
            }

            if (empty($options['expand']) && (empty($param[0]) || $param[0] != $year)) {
                continue;
            }

            if (isset($output[ $year ][ 'months' ][ $month ])) {
                ++$output[ $year ][ 'months' ][ $month ][ 'number' ];
            } else {
                $output[ $year ][ 'months' ][ $month ] = [
                    'number' => 1,
                    'year'   => $year,
                    'month'  => strftime('%b', $value[ 'date_created' ]),
                    'link'   => $this->router->getRoute('news.month', [
                        ':year'  => $year,
                        ':month' => $month,
                        ':id'    => ''
                    ])
                ];
            }
        }
        $output[ 'all' ] = [
            'number' => count($data),
            'year'   => t('All'),
            'link'   => $this->router->getRoute('news.index')
        ];

        return $tpl->addVar('years', $output);
    }

    public function hookBlockNewsArchiveEditForm(&$form, $data)
    {
        $form->group('new-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('new-legend', t('News setting'))
                ->group('limit-group', 'div', function ($form) use ($data) {
                    $form->checkbox('expand', [
                        'class'   => 'form-control',
                        'checked' => $data[ 'options' ][ 'expand' ]
                    ])
                    ->label('limit-label', '<span class="ui"></span>' . t('Expand archives per month'), [
                        'for' => 'expand'
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookNewsBlockArchiveUpdateValidator(&$validator)
    {
        $validator
            ->addRule('expand', 'bool')
            ->addLabel('expand', t('Expand archives per month'));
    }

    public function hookNewsArchiveUpdateBefore($validator, &$values, $id)
    {
        $values[ 'options' ] = json_encode([
            'expand'   => (bool) $validator->getInput('expand')
        ]);
    }

    public function hookBlockNewsLast($tpl, array $options)
    {
        $news = $this->query
            ->from('node')
            ->where('node_status_id', 1)
            ->where('type', 'article')
            ->orderBy('sticky', 'desc')
            ->orderBy('date_created', 'desc')
            ->limit($options[ 'limit' ] + 1, $options[ 'offset' ])
            ->fetchAll();

        $isMore = false;
        foreach ($news as $key => &$value) {
            if ($key > $options[ 'limit' ] - 1) {
                $isMore = $options[ 'more' ];
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
        unset($value);

        return $tpl->addVars([
                'is_link_more' => $isMore,
                'limit'        => $options[ 'limit' ],
                'link_more'    => $this->router->getRoute('news.index'),
                'news'         => $news,
                'offset'       => $options[ 'offset' ]
        ]);
    }

    public function hookBlockNewsLastEditForm(&$form, $data)
    {
        $form->group('new-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('new-legend', t('News setting'))
                ->group('limit-group', 'div', function ($form) use ($data) {
                    $options = [
                        [ 'value' => 1, 'label' => 1 ],
                        [ 'value' => 2, 'label' => 2 ],
                        [ 'value' => 3, 'label' => 3 ],
                        [ 'value' => 4, 'label' => 4 ]
                    ];

                    $form->label('limit-label', t('Number of news to display'))
                    ->select('limit', $options, [
                        'class'    => 'form-control',
                        'max'      => 4,
                        'min'      => 1,
                        'selected' => $data[ 'options' ][ 'limit' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('offset-group', 'div', function ($form) use ($data) {
                    $form->label('offset-label', t('Offset'))
                    ->number('offset', [
                        'class' => 'form-control',
                        'min'   => 0,
                        'value' => $data[ 'options' ][ 'offset' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('more-group', 'div', function ($form) use ($data) {
                    $form->checkbox('more', [
                        'checked' => $data[ 'options' ][ 'more' ]
                    ])
                    ->label('more-label', '<i class="ui" aria-hidden="true"></i> ' . t('Add a "more" link at the bottom of the screen if there is more content'), [
                        'for' => 'more'
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookBlockNewsLastUpdateValidator(&$validator, $id)
    {
        $validator
            ->addRule('limit', 'required|inarray:1,2,3,4')
            ->addRule('offset', 'required|numeric|min_numeric:0')
            ->addRule('more', 'bool');
        $validator
            ->addLabel('limit', t('Nombre de news à afficher'))
            ->addLabel('offset', t('Décalage'))
            ->addLabel('more', t('Ajouter un lien "plus" en bas de l\'affichage'));
    }

    public function hookNewsLastUpdateBefore($validator, &$values, $id)
    {
        $values[ 'options' ] = json_encode([
            'limit'  => (int) $validator->getInput('limit'),
            'offset' => (int) $validator->getInput('offset'),
            'more'   => (bool) $validator->getInput('more')
        ]);
    }
}
