<?php

namespace SoosyzeCore\News\Services;

class HookConfig implements \SoosyzeCore\Config\Services\ConfigInterface
{
    public function menu(&$menu)
    {
        $menu[ 'news' ] = [
            'title_link' => 'News'
        ];
    }

    public function form(&$form, $data, $req)
    {
        $form->group('news-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('news-legend', t('Settings'))
                ->group('news_pagination-group', 'div', function ($form) use ($data) {
                    $form->label('news_pagination-group', t('Number of articles per page'))
                    ->number('news_pagination', [
                        'class'    => 'form-control',
                        'max'      => 50,
                        'min'      => 1,
                        'required' => 1,
                        'value'    => $data[ 'news_pagination' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'news_pagination' => 'required|between_numeric:1,50'
        ])->setLabel([
            'news_pagination' => t('Number of articles per page')
        ]);
    }

    public function before(&$validator, &$data, $id)
    {
        $data = [
            'news_pagination' => (int) $validator->getInput('news_pagination')
        ];
    }

    public function after(&$validator, $data, $id)
    {
    }

    public function files(&$inputsFile)
    {
    }
}
