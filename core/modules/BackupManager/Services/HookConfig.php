<?php

namespace SoosyzeCore\BackupManager\Services;

final class HookConfig implements \SoosyzeCore\Config\Services\ConfigInterface
{
    public function defaultValues()
    {
        return [
            'max_backups'      => 0,
            'backup_cron'      => '',
            'backup_frequency' => ''
        ];
    }

    public function menu(array &$menu)
    {
        $menu[ 'backupmanager' ] = [
            'title_link' => 'Backups'
        ];
    }

    public function form(&$form, array $data, $req)
    {
        $form
            ->group('backups-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('backups-fieldset', t('Backups'))
                ->group('max_backup-group', 'div', function ($form) use ($data) {
                    $form->label('max_backup-label', t('Maximum number of backups'), [
                        'data-tooltip' => t('The maximum number of backups that will be stored at the same time. Then the older backups will be override. Set 0 for untilimited'),
                    ])
                    ->group('max_backups-flex', 'div', function ($form) use ($data) {
                        $form->number('max_backups', [
                            ':actions' => 1,
                            'class'    => 'form-control',
                            'min'      => 0,
                            'value'    => $data[ 'max_backups' ]
                        ]);
                    }, [ 'class' => 'form-group-flex' ]);
                }, [ 'class' => 'form-group' ])
                ->group('backup_frequency-group', 'div', function ($form) use ($data) {
                    $form->label('frequency_backup-label', t('Backup frequency'), [
                        'data-tooltip' => t('Leave the value at 0 so that the frequency is not taken into account'),
                    ])
                    ->text('backup_frequency', [
                        'class'       => 'form-control',
                        'maxlength'   => 255,
                        'placeholder' => '30 min, 1 hour, 1 day, 1 month, 1 year...',
                        'value'       => $data[ 'backup_frequency' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('backup_frequency-info-group', 'div', function ($form) {
                    $form->html('backup_frequency-info', '<a target="_blank" href="https://www.php.net/manual/fr/datetime.formats.relative.php">:content</a>', [
                        ':content' => t('Relative PHP Date Formats')
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('backup_cron-group', 'div', function ($form) use ($data) {
                    $form->checkbox('backup_cron', [ 'checked' => $data[ 'backup_cron' ] ])
                    ->label('backup_cron-label', '<span class="ui"></span> ' . t('Enable CRON backups'), [
                        'for' => 'backup_cron'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('cron_info-group', 'div', function ($form) {
                    $form->html('cron_info', '<a target="_blank" href="https://fr.wikipedia.org/wiki/Cron">:content</a>', [
                        ':content' => t('How to set up the CRON service ?')
                    ]);
                }, [ 'class' => 'form-group' ]);
            });
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'max_backups'      => 'min:0',
            'backup_cron'      => 'bool',
            'backup_frequency' => '!required|string'
        ])->setLabels([
            'max_backups'      => t('Maximum number of backups'),
            'backup_cron'      => t('Enable CRON backups'),
            'backup_frequency' => t('Fréquence des sauvegardes')
        ]);
    }

    public function before(&$validator, array &$data, $id)
    {
        $data = [
            'max_backups'      => $validator->getInput('max_backups'),
            'backup_cron'      => (bool) $validator->getInput('backup_cron'),
            'backup_frequency' => $validator->getInput('backup_frequency')
        ];
    }

    public function after(&$validator, array $data, $id)
    {
    }

    public function files(array &$inputsFile)
    {
    }
}
