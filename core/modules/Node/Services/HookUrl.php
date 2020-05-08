<?php

namespace SoosyzeCore\Node\Services;

use Soosyze\Components\Util\Util;

class HookUrl
{
    /**
     * @var \Queryflatfile\Schema
     */
    private $alias;

    /**
     * @var \Queryflatfile\Schema
     */
    private $schema;

    /**
     * @var \Queryflatfile\Request
     */
    private $query;

    /**
     * @var \Queryflatfile\Schema
     */
    private $config;

    public function __construct($alias, $schema, $query, $config)
    {
        $this->alias  = $alias;
        $this->schema = $schema;
        $this->query  = $query;
        $this->config = $config;
    }

    public function hookCreateFormData(&$data)
    {
        $data[ 'meta_url' ] = '';
    }

    public function hookEditFormData(&$data, $id)
    {
        $data[ 'meta_url' ] = $this->alias->getAlias('node/' . $id, '');
    }

    public function hookCreateForm($form, $data)
    {
        $form->after('seo-legend', function ($form) use ($data) {
            $form->group('meta_url-group', 'div', function ($form) use ($data) {
                $form->label('meta_url-label', t('Url'), [
                    'data-tooltip' => t('Leave blank to automatically generate your URL')
                ])->text('meta_url', [
                    'class'       => 'form-control',
                    'placeholder' => 'page/titre-de-mon-contenu',
                    'value'       => $data[ 'meta_url' ]
                ]);
            }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookStoreValidator($validator)
    {
        $validator->addRule('meta_url', '!required|string|max:255|regex:/^[:a-z0-9-_\/]+$/');
    }

    public function hookStoreAfter($validator)
    {
        if (!($alias = $this->makeAlias($validator))) {
            return;
        }
        $id = $this->schema->getIncrement('node');
        $this->query
            ->insertInto('system_alias_url', [ 'source', 'alias' ])
            ->values([ 'node/' . $id, $alias ])
            ->execute();
    }

    public function hookUpdateValid($validator, $id)
    {
        if (!($alias = $this->makeAlias($validator))) {
            $this->query
                ->delete()
                ->from('system_alias_url')
                ->where('alias', 'node/' . $id)
                ->execute();
        } elseif ($link = $this->alias->getAlias('node/' . $id)) {
            $this->query
                ->update('system_alias_url', [ 'alias' => $alias ])
                ->where('source', 'node/' . $id)
                ->execute();
        } else {
            $this->query
                ->insertInto('system_alias_url', [ 'source', 'alias' ])
                ->values([ 'node/' . $id, $alias ])
                ->execute();
        }
    }

    public function hookDeleteValid($validator, $id)
    {
        $this->query->from('system_alias_url')
            ->where('source', 'node/' . $id)
            ->delete()
            ->execute();
    }

    private function makeAlias($validator)
    {
        $metaUrl = $validator->getInput('meta_url') !== ''
            ? $validator->getInput('meta_url')
            : $this->config->get('settings.node_url_' . $validator->getInput('type'));

        $urlDefault = empty($metaUrl)
            ? $this->config->get('settings.node_default_url', '')
            : $metaUrl;

        if ($urlDefault === '') {
            return '';
        }

        $time = strtotime($validator->getInput('date_created'));

        $alias = str_replace(
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
                $validator->getInput('title'),
                $validator->getInput('type')
            ],
            $urlDefault
        );

        return Util::strSlug($alias, '-', '\/');
    }
}
