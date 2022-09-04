<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\News\Hook;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Form\FormGroupBuilder;
use Soosyze\Components\Router\Route;
use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\Router;
use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\Node\Services\Node;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;
use Soosyze\Core\Modules\System\Services\Alias;
use Soosyze\Core\Modules\Template\Services\Block as ServiceBlock;

/**
 * @phpstan-import-type NodeEntity from \Soosyze\Core\Modules\Node\Extend
 */
class Block implements \Soosyze\Core\Modules\Block\BlockInterface
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

        /** @phpstan-var Route $routeMonth */
        $routeMonth = RouteCollection::getRoute('news.month');
        $paramMonth = $this->router->parseWiths($routeMonth);
        /** @phpstan-var Route $routeYears */
        $routeYears = RouteCollection::getRoute('news.years');
        $paramYear  = $this->router->parseWiths($routeYears);

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
                                'year' => $year
                            ])
                        ]
                    ]
                ];
            }

            if (isset($optionsSelect[ $year ][ 'value' ][ $month ])) {
                continue;
            }

            /** @phpstan-ignore-next-line */
            $optionsSelect[ $year ][ 'value' ][ $month ] = [
                'label' => t_date('M', (int) $value[ 'date_created' ]),
                'value' => $this->router->generateUrl('news.month', [
                    'year'  => $year,
                    'month' => $month
                ])
            ];
        }

        $selected = '#';
        if (!empty($paramMonth)) {
            $selected = $this->router->generateUrl('news.month', [
                'year'  => $paramMonth[ 'year' ],
                'month' => $paramMonth[ 'month' ]
            ]);
        } elseif (!empty($paramYear)) {
            $selected = $this->router->generateUrl('news.years', [
                'year' => $paramYear[ 'year' ]
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

        /** @phpstan-var Route $route */
        $route = RouteCollection::getRoute('news.years');
        $param = $this->router->parseWiths($route);

        $output = [];
        foreach ($data as $value) {
            $year  = date('Y', (int) $value[ 'date_created' ]);
            $month = date('m', (int) $value[ 'date_created' ]);

            if (isset($output[ $year ][ 'number' ])) {
                ++$output[ $year ][ 'number' ];
            } else {
                $output[ $year ] = [
                    'link'   => $this->router->generateUrl('news.years', [
                        'year' => $year
                    ]),
                    'number' => 1,
                    'year'   => $year
                ];
            }

            if (empty($options[ 'expand' ]) && (empty($param[ 'year' ]) || $param[ 'year' ] != $year)) {
                continue;
            }

            if (isset($output[ $year ][ 'months' ][ $month ][ 'number' ])) {
                ++$output[ $year ][ 'months' ][ $month ][ 'number' ];
            } else {
                $output[ $year ][ 'months' ][ $month ] = [
                    'link'   => $this->router->generateUrl('news.month', [
                        'year'  => $year,
                        'month' => $month
                    ]),
                    'month'  => t_date('M', (int) $value[ 'date_created' ]),
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
        /** @phpstan-var array<NodeEntity> $news */
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

            /** @phpstan-var string $alias */
            $alias = $this->alias->getAlias('node/' . $value[ 'id' ], 'node/' . $value[ 'id' ]);

            $value[ 'link_view' ] = $this->router->makeUrl('/' . ltrim($alias, '/'));
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
                        [ 'label' => '1', 'value' => 1 ],
                        [ 'label' => '2', 'value' => 2 ],
                        [ 'label' => '3', 'value' => 3 ],
                        [ 'label' => '4', 'value' => 4 ]
                    ];

                    $form->label('limit-label', t('Number of items to show'))
                    ->select('limit', $options, [
                        ':selected' => $values[ 'options' ][ 'limit' ],
                        'class'     => 'form-control',
                        'max'       => 4,
                        'min'       => 1,
                        'required'  => 1
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('offset-group', 'div', function ($form) use ($values) {
                    $form->label('offset-label', t('Offset (number of items to skip)'))
                    ->number('offset', [
                        ':actions' => 1,
                        'class'    => 'form-control',
                        'min'      => 0,
                        'required' => 1,
                        'value'    => $values[ 'options' ][ 'offset' ]
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
                        ':list' => static function (string $label) use ($optionsMore): string {
                            return implode(', ', $optionsMore);
                        }
                    ]
                ]
        ]);
    }

    public function hookNewsLastBefore(Validator $validator, array &$data): void
    {
        $data[ 'options' ] = json_encode([
            'limit'     => $validator->getInputInt('limit'),
            'more'      => $validator->getInputInt('more'),
            'offset'    => $validator->getInputInt('offset'),
            'text_more' => $validator->getInput('text_more')
        ]);
    }
}
