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
        $menu['backupmanager'] = [
            'title_link' => 'Backups'
        ];
    }

    public function form(&$form, $data)
    {
        return $form
                ->group('config-backups-fieldset', 'fieldset', function ($form) use ($data) {
                        $form
                            ->legend('config-backups-fieldset', t('Backups'))
                            ->label('config-max_backup-label', t('Max number of backups'), [
                            'data-tooltip' => t('The max number of backups that will be stored at the same time. Then the older backups will be overide. Set 0 for untilimited.'),
                            'for'          => 'max_backups'
                            ])
                            ->number('max_backups', [
                                'class'       => 'form-control',
                                'min'         => 0,
                                'value'       => $data[ 'max_backups' ] > 0 ? $data[ 'max_backups' ] : 0
                            ]);


                }, [ 'class' => 'form-group' ])
                ->token('token_backupmanager_config')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'max_backups'    => 'min:0'
        ])->setLabel([
            'max_backups'         => t('Max backup possible')
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'max_backups'    => $validator->getInput('max_backups'),
        ];
    }
}


