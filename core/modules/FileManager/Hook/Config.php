<?php

namespace SoosyzeCore\FileManager\Hook;

final class Config implements \SoosyzeCore\Config\ConfigInterface
{
    const REPLACE_WITH = 1;

    const KEEP_RENAME = 2;

    const KEEP_REFUSE = 3;

    public function defaultValues()
    {
        return [
            'replace_file' => self::REPLACE_WITH
        ];
    }

    public function menu(array &$menu)
    {
        $menu[ 'filemanager' ] = [
            'title_link' => 'File'
        ];
    }

    public function form(&$form, array $data, $req)
    {
        $form->group('file-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('file-legend', t('Behavior of file transfers'))
                ->group('replace_file_1-group', 'div', function ($form) use ($data) {
                    $form->radio('replace_file', [
                        'checked'  => $data[ 'replace_file' ] === self::REPLACE_WITH,
                        'id'       => 'replace_file_1',
                        'required' => 1,
                        'value'    => self::REPLACE_WITH
                    ])->label('replace_file-label', t('Replace the file with the new one'), [
                        'for' => 'replace_file_1'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('replace_file_2-group', 'div', function ($form) use ($data) {
                    $form->radio('replace_file', [
                        'checked'  => $data[ 'replace_file' ] === self::KEEP_RENAME,
                        'id'       => 'replace_file_2',
                        'required' => 1,
                        'value'    => self::KEEP_RENAME
                    ])->label('replace_file-label', t('Keep the file by renaming the new'), [
                        'for' => 'replace_file_2'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('replace_file_3-group', 'div', function ($form) use ($data) {
                    $form->radio('replace_file', [
                        'checked'  => $data[ 'replace_file' ] === self::KEEP_REFUSE,
                        'id'       => 'replace_file_3',
                        'required' => 1,
                        'value'    => self::KEEP_REFUSE
                    ])->label('replace_file-label', t('Keep the file by refusing the new one'), [
                        'for' => 'replace_file_3'
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'replace_file' => 'required|between_numeric:1,3'
        ]);
    }

    public function before(&$validator, array &$data, $id)
    {
        $data = [
            'replace_file' => (int) $validator->getInput('replace_file')
        ];
    }

    public function after(&$validator, array $data, $id)
    {
    }

    public function files(array &$inputsFile)
    {
    }
}
