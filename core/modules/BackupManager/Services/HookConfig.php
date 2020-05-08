<?php

namespace SoosyzeCore\BackupManager\Services;

class HookConfig
{
    /**
     * @var \Soosyze\Config
     */
    protected $config;

    protected $router;

    public function __construct($config, $router)
    {
        $this->config = $config;
        $this->router = $router;
    }

    public function menu(&$menu)
    {
        $menu[ 'backupmanager' ] = [
            'title_link' => 'Backups'
        ];
    }

    public function form(&$form, $data)
    {
        $form
            ->group('backups-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('backups-fieldset', t('Backups'))
                ->group('max_backup-group', 'div', function ($form) use ($data) {
                    $form->label('max_backup-label', t('Maximum number of backups'), [
                        'data-tooltip' => t('The maximum number of backups that will be stored at the same time. Then the older backups will be override. Set 0 for untilimited'),
                        'for'          => 'max_backups'
                    ])
                    ->number('max_backups', [
                        'class' => 'form-control',
                        'min'   => 0,
                        'value' => $data[ 'max_backups' ] > 0
                            ? $data[ 'max_backups' ]
                            : 0
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('backup_cron-group', 'div', function ($form) use ($data) {
                    $form->checkbox('backup_cron', [ 'checked' => $data[ 'backup_cron' ] ])
                    ->label('backup_cron-label', '<span class="ui"></span> ' . t('Enable CRON backups'), [
                        'for' => 'backup_cron'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('cron_info-group', 'div', function ($form) {
                    $form->html('cron_info', '<a target="_blank" href="https://fr.wikipedia.org/wiki/Cron">:_content</a>', [
                        '_content' => t('How to set up the CRON service ?')
                    ]);
                }, [ 'class' => 'form-group' ]);
            });
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'max_backups' => 'min:0',
            'backup_cron' => 'bool'
        ])->setLabel([
            'max_backups' => t('Maximum number of backups'),
            'backup_cron' => t('Enable CRON backups')
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'max_backups' => $validator->getInput('max_backups'),
            'backup_cron' => (bool) $validator->getInput('backup_cron'),
        ];
    }
}
