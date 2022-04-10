<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Hook;

use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\QueryBuilder\Services\Query;

final class Config implements \SoosyzeCore\Config\ConfigInterface
{
    public const CRON = false;

    public const DEFAULT_URL = ':node_type/:node_title';

    public const MARKDOWN = false;

    /**
     * @var array
     */
    private static $attrGrp = [ 'class' => 'form-group' ];

    /**
     * @var array
     */
    private $nodeTypes = [];

    public function __construct(Query $query)
    {
        $this->nodeTypes = $query
            ->from('node_type')
            ->fetchAll();
    }

    public function defaultValues(): array
    {
        return [
            'node_default_url' => self::DEFAULT_URL,
            'node_cron'        => self::CRON,
            'node_markdown'    => self::MARKDOWN
        ];
    }

    public function menu(array &$menu): void
    {
        $menu[ 'node' ] = [
            'title_link' => 'Node'
        ];
    }

    public function form(FormBuilder &$form, array $data, ServerRequestInterface $req): void
    {
        $form->group('node_markdown-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('node_markdown-legend', t('Markdown'))
                ->group('node_markdown-group', 'div', function ($form) use ($data) {
                    $form->checkbox('node_markdown', [ 'checked' => $data[ 'node_markdown' ] ])
                    ->label('node_markdown-label', '<span class="ui"></span> ' . t('Enable Markdown format'), [
                        'for' => 'node_markdown'
                    ]);
                }, self::$attrGrp)
                ->group('cron_info-group', 'div', function ($form) {
                    $form->html('markdown_info', '<p>:content</p>', [
                        ':content' => t('The Markdown format does not prevent the use of HTML tags, but the default text editor is not suitable for this format.')
                    ])
                    ->html('markdown_info-link', '<p><a target="_blank" href="https://fr.wikipedia.org/wiki/Markdown">:content</a></p>', [
                        ':content' => t('Learn more about the Markdown format')
                    ]);
                }, self::$attrGrp);
        })
            ->group('node_default_url-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('node_default_url-legend', t('Url'))
                ->group('node_default_url-group', 'div', function ($form) use ($data) {
                    $form->label('node_default_url-label', t('Default url'), [
                        'data-tooltip' => t('Applies to all types of content if the templates below are empty')
                    ])
                    ->text('node_default_url', [
                        'class' => 'form-control',
                        'value' => $data[ 'node_default_url' ]
                    ]);
                }, self::$attrGrp);
                foreach ($this->nodeTypes as $nodeType) {
                    $form->group('node_url_' . $nodeType[ 'node_type' ] . '-group', 'div', function ($form) use ($data, $nodeType) {
                        $form->label('node_url_' . $nodeType[ 'node_type' ] . '-label', t($nodeType[ 'node_type_name' ]))
                        ->text('node_url_' . $nodeType[ 'node_type' ], [
                            'class' => 'form-control',
                            'value' => $data[ 'node_url_' . $nodeType[ 'node_type' ] ] ?? ''
                        ]);
                    }, self::$attrGrp);
                }
                $form->html('node_default_url-info', '<p>:content</p>', [
                    ':content' => t('Variables allowed for all') .
                    ' <code>:date_created_year</code>, <code>:date_created_month</code>, <code>:date_created_day</code>, ' .
                    '<code>:node_id</code>, <code>:node_title</code>, <code>:node_type</code>'
                ]);
            })
            ->group('node_cron-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('node_cron-legend', t('Published'))
                ->group('node_cron-group', 'div', function ($form) use ($data) {
                    $form->checkbox('node_cron', [ 'checked' => $data[ 'node_cron' ] ])
                    ->label('node_cron-label', '<span class="ui"></span> ' . t('Activate automatic publication of CRON content'), [
                        'for' => 'node_cron'
                    ]);
                }, self::$attrGrp)
                ->group('cron_info-group', 'div', function ($form) {
                    $form->html('cron_info', '<a target="_blank" href="https://fr.wikipedia.org/wiki/Cron">:content</a>', [
                        ':content' => t('How to set up the CRON service ?')
                    ]);
                }, self::$attrGrp);
            });
    }

    public function validator(Validator &$validator): void
    {
        $validator->setRules([
            'node_default_url' => '!required|string|max:255|regex:/^[-:\w\d_\/]+$/',
            'node_cron'        => 'bool'
        ])->setLabels([
            'node_default_url' => t('Default url'),
            'node_cron'        => t('Default url'),
        ])->addMessage('node_default_url', [
            'regex' => [
                'must' => t('The: label field must contain allowed variables, alphanumeric characters, slashes (/), hyphens (-) or underscores (_).')
            ]
        ]);
        foreach ($this->nodeTypes as $nodeType) {
            $validator->addRule('node_url_' . $nodeType[ 'node_type' ], '!required|string|max:255|regex:/^[-:\w\d_\/]+$/')
                ->addLabel('node_url_' . $nodeType[ 'node_type' ], t($nodeType[ 'node_type_name' ]))
                ->addMessage('meta_url', [
                    'regex' => [
                        'must' => t('The: label field must contain allowed variables, alphanumeric characters, slashes (/), hyphens (-) or underscores (_).')
                    ]
                ]);
        }
    }

    public function before(Validator &$validator, array &$data, string $id): void
    {
        $data = [
            'node_default_url' => $validator->getInput('node_default_url'),
            'node_cron'        => (bool) $validator->getInput('node_cron'),
            'node_markdown'    => (bool) $validator->getInput('node_markdown')
        ];

        foreach ($this->nodeTypes as $nodeType) {
            $key = 'node_url_' . $nodeType[ 'node_type' ];

            $data[ $key ] = $validator->getInput($key);
        }
    }

    public function after(Validator &$validator, array $data, string $id): void
    {
    }

    public function files(array &$inputsFile): void
    {
    }
}
