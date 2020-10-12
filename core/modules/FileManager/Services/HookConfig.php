<?php

namespace SoosyzeCore\FileManager\Services;

final class HookConfig implements \SoosyzeCore\Config\Services\ConfigInterface
{
    public function menu(&$menu)
    {
        $menu[ 'filemanager' ] = [
            'title_link' => 'File'
        ];
    }

    public function form(&$form, $data, $req)
    {
        $form->group('file-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('file-legend', t('Behavior of file transfers'))
                ->group('replace_file_1-group', 'div', function ($form) use ($data) {
                    $form->radio('replace_file', [
                        'checked'  => $data[ 'replace_file' ] === 1,
                        'id'       => 'replace_file_1',
                        'required' => 1,
                        'value'    => 1
                    ])->label('replace_file-label', t('Replace the file with the new one'), [
                        'for' => 'replace_file_1'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('replace_file_2-group', 'div', function ($form) use ($data) {
                    $form->radio('replace_file', [
                        'checked'  => $data[ 'replace_file' ] === 2,
                        'id'       => 'replace_file_2',
                        'required' => 1,
                        'value'    => 2
                    ])->label('replace_file-label', t('Keep the file by renaming the new'), [
                        'for' => 'replace_file_2'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('replace_file_3-group', 'div', function ($form) use ($data) {
                    $form->radio('replace_file', [
                        'checked'  => $data[ 'replace_file' ] === 3,
                        'id'       => 'replace_file_3',
                        'required' => 1,
                        'value'    => 3
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

    public function before(&$validator, &$data, $id)
    {
        $data = [
            'replace_file' => (int) $validator->getInput('replace_file')
        ];
    }

    public function after(&$validator, $data, $id)
    {
    }

    public function files(&$inputsFile)
    {
    }
}
