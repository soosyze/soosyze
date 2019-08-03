<?php

namespace SoosyzeCore\News\Services;

class HookConfig
{
    protected $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function menu(&$menu)
    {
        $menu[] = [
            'key'        => 'news',
            'title_link' => 'Blog'
        ];
    }

    public function form(&$form, $data)
    {
        return $form->group('news-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('news-legend', 'Paramètres')
                    ->group('news-news_pagination-group', 'div', function ($form) use ($data) {
                        $form->label('news-news_pagination-group', 'Nombre d\'articles par page')
                        ->number('news_pagination', [
                            'class'    => 'form-control',
                            'max'      => 50,
                            'min'      => 1,
                            'required' => 1,
                            'value'    => $data[ 'news_pagination' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
        })
                ->token('token_system_config')
                ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'news_pagination' => 'required|int|between:1,50'
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'news_pagination' => (int) $validator->getInput('news_pagination')
        ];
    }
}
