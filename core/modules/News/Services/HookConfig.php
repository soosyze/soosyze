<?php

namespace SoosyzeCore\News\Services;

class HookConfig implements \SoosyzeCore\Config\Services\ConfigInterface
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function menu(&$menu)
    {
        $menu[ 'news' ] = [
            'title_link' => 'News'
        ];
    }

    public function form(&$form, $data, $req)
    {
        $form->group('news_pagination-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('news_pagination-legend', t('Settings'))
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
        })
            ->group('new_default_image-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('new_default_image-legend', t('Image par défaut'))
                ->group('new_default_image-group', 'div', function ($form) use ($data) {
                    $form->label('new_default_image-label', t('Image par défaut'), [
                        'class'        => 'control-label',
                        'data-tooltip' => '200ko maximum.',
                        'for'          => 'new_default_image'
                    ]);
                    $this->file->inputFile('new_default_image', $form, $data[ 'new_default_image' ]);
                }, [ 'class' => 'form-group' ])
                ->group('new_default_icon-group', 'div', function ($form) use ($data) {
                    $form->label('new_default_icon-group', t('Icone par défaut'), [
                        'data-tooltip' => t('Icon FontAwesome en cas d\'absence d\'une image par défaut')
                    ])
                    ->text('new_default_icon', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $data[ 'new_default_icon' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            });
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'new_default_icon'  => 'required|string|fontawesome:solid,brands',
            'new_default_image' => '!required|image|max:200Kb',
            'news_pagination'   => 'required|between_numeric:1,50'
        ])->setLabel([
            'new_default_icon'  => t('Icone par défaut'),
            'new_default_image' => t('Image par défaut'),
            'news_pagination'   => t('Number of articles per page')
        ]);
    }

    public function before(&$validator, &$data, $id)
    {
        $data = [
            'new_default_icon' => $validator->getInput('new_default_icon'),
            'news_pagination'  => (int) $validator->getInput('news_pagination')
        ];
    }

    public function after(&$validator, $data, $id)
    {
    }

    public function files(&$inputsFile)
    {
        $inputsFile = [ 'new_default_image' ];
    }
}
