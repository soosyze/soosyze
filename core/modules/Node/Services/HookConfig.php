<?php

namespace SoosyzeCore\Node\Services;

class HookConfig
{
    protected $nodeTypes = [];

    public function __construct($query)
    {
        $this->nodeTypes = $query
            ->from('node_type')
            ->fetchAll();
    }

    public function menu(&$menu)
    {
        $menu[ 'node' ] = [
            'title_link' => 'Node'
        ];
    }

    public function form(&$form, $data)
    {
        return $form->group('node_default_url-fieldset', 'fieldset', function ($form) use ($data)
                {
                    $form->legend('node_default_url-legend', t('Url'))
                    ->group('node_default_url-group', 'div', function ($form) use ($data)
                    {
                        $form->label('node_default_url-label', t('Default url'), [
                            'data-tooltip' => t('Applies to all types of content if the templates below are empty')
                        ])
                        ->text('node_default_url', [
                            'class' => 'form-control',
                            'value' => $data[ 'node_default_url' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
                    foreach( $this->nodeTypes as $nodeType )
                    {
                        $form->group('node_url_' . $nodeType[ 'node_type' ] . '-group', 'div', function ($form) use ($data, $nodeType)
                        {
                            $form->label('node_url_' . $nodeType[ 'node_type' ] . '-label', t($nodeType[ 'node_type_name' ]))
                            ->text('node_url_' . $nodeType[ 'node_type' ], [
                                'class' => 'form-control',
                                'value' => isset($data[ 'node_url_' . $nodeType[ 'node_type' ] ])
                                ? $data[ 'node_url_' . $nodeType[ 'node_type' ] ]
                                : ''
                            ]);
                        }, [ 'class' => 'form-group' ]);
                    }
                    $form->html('node_default_url-info', '<p>:_content</p>', [
                        '_content' => t('Variables allowed for all') .
                        ' <code>:date_created_year</code>, <code>:date_created_month</code>, <code>:date_created_day</code>, ' .
                        '<code>:node_id</code>, <code>:node_title</code>, <code>:node_type</code>'
                    ]);
                })
                ->group('node_cron-fieldset', 'fieldset', function ($form) use ($data)
                {
                    $form->legend('node_cron-legend', t('Published'))
                    ->group('node_cron-group', 'div', function ($form) use ($data)
                    {
                        $form->checkbox('node_cron', [ 'checked' => $data[ 'node_cron' ] ])
                        ->label('node_cron-label', '<span class="ui"></span> ' . t('Activé la publication automatique des contenus CRON'), [
                            'for' => 'node_cron'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('cron_info-group', 'div', function ($form)
                    {
                        $form->html('cron_info', '<a target="_blank" href="https://fr.wikipedia.org/wiki/Cron">:_content</a>', [
                            '_content' => t('How to set up the CRON service ?')
                        ]);
                    }, [ 'class' => 'form-group' ]);
                });
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'node_default_url' => '!required|string|max:255|regex:/^[:a-z0-9-_\/]+/',
            'node_cron'        => 'bool'
        ])->setLabel([
            'node_default_url' => t('Url par défaut'),
            'node_cron'        => t('Url par défaut'),
        ]);
        foreach( $this->nodeTypes as $nodeType) {
            $validator->addRule('node_url_' . $nodeType[ 'node_type' ], '!required|string|max:255|regex:/^[:a-z0-9-_\/]+/')
                ->addLabel('node_url_' . $nodeType[ 'node_type' ], t($nodeType[ 'node_type_name' ]));
        }
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'node_default_url' => $validator->getInput('node_default_url'),
            'node_cron'        => (bool) $validator->getInput('node_cron')
        ];

        foreach ($this->nodeTypes as $nodeType) {
            $data[ 'node_url_' . $nodeType[ 'node_type' ] ] = $validator->getInput('node_url_' . $nodeType[ 'node_type' ]);
        }
    }
}
