<?php

namespace SoosyzeCore\Node\Services;

use Soosyze\Components\Util\Util;

class HookUrl
{
    /**
     * @var \Queryflatfile\Schema
     */
    private $schema;

    /**
     * @var \Queryflatfile\Request
     */
    private $query;

    /**
     * Si l'alias .existe.
     *
     * @var bool
     */
    private $is_alias;

    public function __construct($schema, $query, $config)
    {
        $this->schema = $schema;
        $this->query  = $query;
        $this->config = $config;

        $this->is_alias = $this->schema->hasTable('system_alias_url');
    }

    public function hookCreateFormData(&$data)
    {
        if ($this->is_alias) {
            $data[ 'meta_url' ] = '';
        }
    }

    public function hookEditFormData(&$data, $id)
    {
        if ($this->is_alias) {
            $data[ 'meta_url' ] = '';
            $link               = $this->query
                ->from('system_alias_url')
                ->where('source', '==', 'node/' . $id)
                ->fetch();

            if ($link) {
                $data[ 'meta_url' ] = $link[ 'alias' ];
            }
        }
    }

    public function hookCreateForm($form, $data)
    {
        if ($this->is_alias) {
            $form->after('seo-legend', function ($form) use ($data) {
                $form->group('meta_url-group', 'div', function ($form) use ($data) {
                    $form->label('meta_url-label', t('Url'), [
                        'data-tooltip' => t('Laisser vide pour générer automatiquement votre URL')
                    ])->text('meta_url', [
                        'class'       => 'form-control',
                        'placeholder' => 'page/titre-de-mon-contenu',
                        'value'       => $data[ 'meta_url' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            });
        }
    }

    public function hookStoreValidator($validator)
    {
        if ($this->is_alias) {
            $validator->addRule('meta_url', '!required|string|max:255|regex:/^[:a-z0-9-_\/]+$/');
        }
    }

    public function hookStoreAfter($validator)
    {
        if ($this->is_alias) {
            if (!($alias = $this->makeAlias($validator))) {
                return;
            }
            $id = $this->schema->getIncrement('node');
            $this->query
                ->insertInto('system_alias_url', [ 'source', 'alias' ])
                ->values([ 'node/' . $id, $alias ])
                ->execute();
        }
    }
    
    public function hookUpdateValid($validator, $id)
    {
        if ($this->is_alias) {
            $link = $this->query
                ->from('system_alias_url')
                ->where('source', '==', 'node/' . $id)
                ->fetch();

            if (!($alias = $this->makeAlias($validator))) {
                $this->query->delete()
                    ->from('system_alias_url')
                    ->where('alias', '==', 'node/' . $id)
                    ->execute();
            } elseif ($link) {
                $this->query->update('system_alias_url', [ 'alias' => $alias ])
                    ->where('source', '==', 'node/' . $id)
                    ->execute();
            } else {
                $this->query
                    ->insertInto('system_alias_url', [ 'source', 'alias' ])
                    ->values([ 'node/' . $id, $alias ])
                    ->execute();
            }
        }
    }

    public function hookDeleteValid($validator, $id)
    {
        if ($this->is_alias) {
            $this->query->from('system_alias_url')
                ->where('source', '==', 'node/' . $id)
                ->delete()
                ->execute();
        }
    }
    
    private function makeAlias($validator)
    {
        $alias = $validator->getInput('meta_url') !== ''
            ? $validator->getInput('meta_url')
            : $this->config->get('settings.node_url_' . $validator->getInput('type'));
        $alias = $alias === ''
            ? $this->config->get('settings.node_default_url')
            : $alias;

        $time = strtotime($validator->getInput('date_created'));

        return str_replace(
            [
                ':date_created_year',
                ':date_created_month',
                ':date_created_day',
                ':node_title',
                ':node_type'
            ],
            [
                date('Y', $time),
                date('m', $time),
                date('d', $time),
                Util::strSlug($validator->getInput('title'), '-'),
                $validator->getInput('type')
            ],
            $alias
        );
    }
}
