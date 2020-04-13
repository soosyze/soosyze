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
        return $form->group('node_default_url-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('node_default_url-legend', t('Url'))
                    ->group('node_default_url-group', 'div', function ($form) use ($data) {
                        $form->label('node_default_url-label', t('Default url'), [
                            'data-tooltip' => t('Applies to all types of content if the templates below are empty')
                        ])
                        ->text('node_default_url', [
                            'class' => 'form-control',
                            'value' => $data[ 'node_default_url' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
            foreach ($this->nodeTypes as $nodeType) {
                $form->group('node_url_' . $nodeType[ 'node_type' ] . '-group', 'div', function ($form) use ($data, $nodeType) {
                    $form->label('node_url_' . $nodeType[ 'node_type' ] . '-label', t($nodeType[ 'node_type_name' ]))
                            ->text('node_url_' . $nodeType[ 'node_type' ], [
                                'class' => 'form-control',
                                'value' => isset($data[ 'node_url_' . $nodeType[ 'node_type' ] ])
                                ? $data[ 'node_url_' . $nodeType[ 'node_type' ] ]
                                : ''
                        ]);
                }, [ 'class' => 'form-group' ]);
            }
            $form->html('cancel', '<p>:_content</p>', [
                    '_content' => t('Variables allowed for all') .
                    ' <code>:date_created_year</code>, <code>:date_created_month</code>, <code>:date_created_day</code>, ' .
                    '<code>:node_id</code>, <code>:node_title</code>, <code>:node_type</code>'
                ]);
        });
    }

    public function validator(&$validator)
    {
        $validator->addRule('node_default_url', '!required|string|max:255|regex:/^[:a-z0-9-_\/]+/')
            ->addLabel('node_default_url', t('Url par défaut'));
        foreach ($this->nodeTypes as $nodeType) {
            $validator->addRule('node_url_' . $nodeType[ 'node_type' ], '!required|string|max:255|regex:/^[:a-z0-9-_\/]+/')
                ->addLabel('node_url_' . $nodeType[ 'node_type' ], t($nodeType[ 'node_type_name' ]));
        }
    }

    public function before(&$validator, &$data)
    {
        $data[ 'node_default_url' ] = $validator->getInput('node_default_url');
        foreach ($this->nodeTypes as $nodeType) {
            $data[ 'node_url_' . $nodeType[ 'node_type' ] ] = $validator->getInput('node_url_' . $nodeType[ 'node_type' ]);
        }
    }
}
