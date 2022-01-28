<?php

declare(strict_types=1);

namespace SoosyzeCore\News\Hook;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Form\FormGroupBuilder;
use Soosyze\Components\Router\Router;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Node\Services\Node;
use SoosyzeCore\QueryBuilder\Services\Query;
use SoosyzeCore\System\Services\Alias;
use SoosyzeCore\Template\Services\Block as ServiceBlock;

class Block implements \SoosyzeCore\Block\BlockInterface
{
    public const MORE_LINK_NOT_ADD = 0;

    public const MORE_LINK_ADD = 1;

    public const MORE_LINK_ADD_IF = 2;

    private const PATH_VIEWS = __DIR__ . '/../Views/';

    /**
     * @var Alias
     */
    private $alias;

    /**
     * @var Node
     */
    private $node;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Router
     */
    private $router;

    public function __construct(
        Alias $alias,
        Node $node,
        Query $query,
        Router $router
    ) {
        $this->alias  = $alias;
        $this->node   = $node;
        $this->query  = $query;
        $this->router = $router;
    }

    public function hookBlockCreateFormData(array &$blocks): void
    {
        $blocks[ 'news.archive' ]        = [
            'description' => t('List of articles by year and month.'),
            'icon'        => 'fas fa-archive',
            'hook'        => 'news.archive',
            'options'     => [ 'expand' => false ],
            'path'        => self::PATH_VIEWS,
            'title'       => t('Archives list'),
            'tpl'         => 'components/block/news-archive.php'
        ];
        $blocks[ 'news.archive.select' ] = [
            'description' => t('Selection list of articles by year and month.'),
            'icon'        => 'fas fa-archive',
            'hook'        => 'news.archive.select',
            'path'        => self::PATH_VIEWS,
            'title'       => t('Archives select'),
            'tpl'         => 'components/block/news-archive_select.php'
        ];
        $blocks[ 'news.last' ]           = [
            'description' => t('Displays the latest news.'),
            'icon'        => 'fas fa-newspaper',
            'hook'        => 'news.last',
            'options'     => [
                'limit'     => 3,
                'offset'    => 0,
                'more'      => self::MORE_LINK_NOT_ADD,
                'text_more' => t('Show blog')
            ],
            'path'        => self::PATH_VIEWS,
            'title'       => t('Last news'),
            'tpl'         => 'components/block/news-last.php'
        ];
    }

    public function hookNewsArchiveSelect(ServiceBlock $tpl): ServiceBlock
    {
        $data = $this->query
            ->from('node')
            ->where('node_status_id', '=', 1)
            ->where('type', '=', 'article')
            ->fetchAll();

        $query      = $this->router->parseQueryFromRequest();
        $paramMonth = $this->router->parseParam('news/:year/:month:id', $query, [
            ':year'  => '\d{4}',
            ':month' => '0[1-9]|1[0-2]',
            ':id'    => '(/page/[1-9]\d*)?'
        ]);
        $paramYear  = $this->router->parseParam('news/:year:id', $query, [
            ':year' => '\d{4}',
            ':id'   => '(/page/[1-9]\d*)?'
        ]);

        $optionsSelect[] = [
            'label' => t('-- Select --'),
            'value' => $this->router->generateUrl('news.index')
        ];
        foreach ($data as $value) {
            $year  = date('Y', (int) $value[ 'date_created' ]);
            $month = date('m', (int) $value[ 'date_created' ]);

            if (!isset($optionsSelect[ $year ])) {
                $optionsSelect[ $year ] = [
                    'label' => $year,
                    'value' => [
                        $year => [
                            'label' => t('All :year', [ ':year' => $year ]),
                            'value' => $this->router->generateUrl('news.years', [
                                ':year' => $year,
                                ':id'   => ''
                            ])
                        ]
                    ]
                ];
            }

            if (isset($optionsSelect[ $year ][ 'value' ][ $month ])) {
                continue;
            }

            $optionsSelect[ $year ][ 'value' ][ $month ] = [
                'label' => strftime('%b', (int) $value[ 'date_created' ]),
                'value' => $this->router->generateUrl('news.month', [
                    ':year'  => $year,
                    ':month' => $month,
                    ':id'    => ''
                ])
            ];
        }

        $selected = '#';
        if (!empty($paramMonth)) {
            $selected = $this->router->generateUrl('news.month', [
                ':year'  => $paramMonth[ 0 ],
                ':month' => $paramMonth[ 1 ],
                ':id'    => ''
            ]);
        } elseif (!empty($paramYear)) {
            $selected = $this->router->generateUrl('news.years', [
                ':year' => $paramYear[ 0 ],
                ':id'   => ''
            ]);
        }

        $form = (new FormBuilder([ 'method' => 'get', 'action' => '#' ]))
            ->select('archives', $optionsSelect, [
            ':selected' => $selected,
            'class'     => 'form-control',
            'onchange'  => 'this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);'
        ]);

        return $tpl->addVar('form', $form);
    }

    public function hookNewsArchive(ServiceBlock $tpl, array $options): ServiceBlock
    {
        $data = $this->query
            ->from('node')
            ->where('node_status_id', '=', 1)
            ->where('type', '=', 'article')
            ->fetchAll();

        $query = $this->router->parseQueryFromRequest();
        $param = $this->router->parseParam('news/:year:id', $query, [
            ':year' => '\d{4}',
            ':id'   => '(/page/[1-9]\d*)?'
        ]);

        $output = [];
        foreach ($data as $value) {
            $year  = date('Y', (int) $value[ 'date_created' ]);
            $month = date('m', (int) $value[ 'date_created' ]);

            if (isset($output[ $year ])) {
                ++$output[ $year ][ 'number' ];
            } else {
                $output[ $year ] = [
                    'link'   => $this->router->generateUrl('news.years', [
                        ':year' => $year,
                        ':id'   => ''
                    ]),
                    'number' => 1,
                    'year'   => $year
                ];
            }

            if (empty($options[ 'expand' ]) && (empty($param[ 0 ]) || $param[ 0 ] != $year)) {
                continue;
            }

            if (isset($output[ $year ][ 'months' ][ $month ])) {
                ++$output[ $year ][ 'months' ][ $month ][ 'number' ];
            } else {
                $output[ $year ][ 'months' ][ $month ] = [
                    'link'   => $this->router->generateUrl('news.month', [
                        ':year'  => $year,
                        ':month' => $month,
                        ':id'    => ''
                    ]),
                    'month'  => strftime('%b', (int) $value[ 'date_created' ]),
                    'number' => 1,
                    'year'   => $year
                ];
            }
        }
        $output[ 'all' ] = [
            'number' => count($data),
            'year'   => t('All'),
            'link'   => $this->router->generateUrl('news.index')
        ];

        return $tpl->addVar('years', $output);
    }

    public function hookNewsArchiveForm(FormGroupBuilder &$form, array $values): void
    {
        $form->group('new-fieldset', 'fieldset', function ($form) use ($values) {
            $form->legend('new-legend', t('Settings'))
                ->group('limit-group', 'div', function ($form) use ($values) {
                    $form->checkbox('expand', [
                        'checked' => $values[ 'options' ][ 'expand' ],
                        'class'   => 'form-control'
                    ])
                    ->label('limit-label', '<span class="ui"></span>' . t('Expand archives per month'), [
                        'for' => 'expand'
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookNewsArchiveValidator(Validator &$validator): void
    {
        $validator
            ->addRule('expand', 'bool')
            ->addLabel('expand', t('Expand archives per month'));
    }

    public function hookNewsArchiveBefore(Validator $validator, array &$data): void
    {
        $data[ 'options' ] = json_encode([
            'expand' => (bool) $validator->getInput('expand')
        ]);
    }

    public function hookNewsLast(ServiceBlock $tpl, array $options): ServiceBlock
    {
        $news = $this->query
            ->from('node')
            ->where('node_status_id', '=', 1)
            ->where('type', '=', 'article')
            ->orderBy('sticky', SORT_DESC)
            ->orderBy('date_created', SORT_DESC)
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

            $alias = $this->alias->getAlias('node/' . $value[ 'id' ], 'node/' . $value[ 'id' ]);

            $value[ 'link_view' ] = $this->router->makeRoute($alias);
        }
        unset($value);

        return $tpl->addVars([
                'is_link_more' => $options[ 'more' ] === 1 || ($options[ 'more' ] === 2 && $isMore),
                'limit'        => $options[ 'limit' ],
                'link_more'    => $this->router->generateUrl('news.index'),
                'news'         => $news,
                'offset'       => $options[ 'offset' ],
                'text_more'    => $options[ 'text_more' ],
        ]);
    }

    public function hookNewsLastForm(FormGroupBuilder &$form, array $values): void
    {
        $form->group('new-fieldset', 'fieldset', function ($form) use ($values) {
            $form->legend('new-legend', t('Settings'))
                ->group('limit-group', 'div', function ($form) use ($values) {
                    $options = [
                        [ 'label' => 1, 'value' => 1 ],
                        [ 'label' => 2, 'value' => 2 ],
                        [ 'label' => 3, 'value' => 3 ],
                        [ 'label' => 4, 'value' => 4 ]
                    ];

                    $form->label('limit-label', t('Number of items to show'))
                    ->select('limit', $options, [
                        ':selected' => $values[ 'options' ][ 'limit' ],
                        'class'     => 'form-control',
                        'max'       => 4,
                        'min'       => 1
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('offset-group', 'div', function ($form) use ($values) {
                    $form->label('offset-label', t('Offset (number of items to skip)'))
                    ->number('offset', [
                        'class' => 'form-control',
                        'min'   => 0,
                        'value' => $values[ 'options' ][ 'offset' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->legend('more-legend', t('More link'))
                ->group('more_0-group', 'div', function ($form) use ($values) {
                    $form->radio('more', [
                        'checked' => $values[ 'options' ][ 'more' ] === self::MORE_LINK_NOT_ADD,
                        'id'      => 'more_0',
                        'value'   => 0
                    ])
                    ->label('more-label', '<i class="ui" aria-hidden="true"></i> ' . t('Do not add a "more" link'), [
                        'for' => 'more_0'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('more_1-group', 'div', function ($form) use ($values) {
                    $form->radio('more', [
                        'checked' => $values[ 'options' ][ 'more' ] === self::MORE_LINK_ADD,
                        'id'      => 'more_1',
                        'value'   => 1
                    ])
                    ->label('more-label', '<i class="ui" aria-hidden="true"></i> ' . t('Add a "more" link'), [
                        'for' => 'more_1'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('more_2-group', 'div', function ($form) use ($values) {
                    $form->radio('more', [
                        'checked' => $values[ 'options' ][ 'more' ] === self::MORE_LINK_ADD_IF,
                        'id'      => 'more_2',
                        'value'   => 2
                    ])
                    ->label('more-label', '<i class="ui" aria-hidden="true"></i> ' . t('Add a "more" link if there is more content'), [
                        'for' => 'more_2'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('text_more-group', 'div', function ($form) use ($values) {
                    $form->label('text_more-label', t('More link text'))
                    ->text('text_more', [
                        'class'     => 'form-control',
                        'maxlength' => 128,
                        'value'     => $values[ 'options' ][ 'text_more' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookNewsLastValidator(Validator &$validator): void
    {
        $optionsMore = [
            self::MORE_LINK_NOT_ADD => t('Do not add a "more" link'),
            self::MORE_LINK_ADD     => t('Add a "more" link'),
            self::MORE_LINK_ADD_IF  => t('Add a "more" link if there is more content')
        ];

        $validator
            ->addRule('limit', 'required|inarray:1,2,3,4')
            ->addRule('offset', 'required|numeric|min_numeric:0')
            ->addRule('more', 'required|inarray:' . implode(', ', array_keys($optionsMore)))
            ->addRule('text_more', 'required_with:more|string|max:255');
        $validator
            ->addLabel('limit', t('Number of items to show'))
            ->addLabel('offset', t('Offset (number of items to skip)'))
            ->addLabel('more', t('More link'))
            ->addLabel('text_more', t('More link text'));
        $validator
            ->setAttributs([
                'more' => [
                    'inarray' => [
                        ':list' => static function (string $label) use ($optionsMore) {
                            return implode(', ', $optionsMore);
                        }
                    ]
                ]
        ]);
    }

    public function hookNewsLastBefore(Validator $validator, array &$data): void
    {
        $data[ 'options' ] = json_encode([
            'limit'     => (int) $validator->getInput('limit'),
            'more'      => (int) $validator->getInput('more'),
            'offset'    => (int) $validator->getInput('offset'),
            'text_more' => $validator->getInput('text_more')
        ]);
    }
}
