<?php

namespace User\Services;

class HookConfig
{
    /**
     * @var \Soosyze\Config
     */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function menu(&$menu)
    {
        $menu[] = [
            'key'        => 'user',
            'title_link' => 'Utilisateur'
        ];
    }

    public function form(&$form, $data)
    {
        return $form->group('config-inscription-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('config-inscription-legend', 'Inscription')
                    ->group('config-register-group', 'div', function ($form) use ($data) {
                        $form->checkbox('user_register', 'user_register', [ 'checked' => $data[ 'user_register' ] ])
                        ->label('config-register-label', '<span class="ui"></span> Ouvrir l\'inscription.', [
                            'for' => 'user_register'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('config-relogin-group', 'div', function ($form) use ($data) {
                        $form->checkbox('user_relogin', 'user_relogin', [ 'checked' => $data[ 'user_relogin' ] ])
                        ->label('config-relogin-label', '<span class="ui"></span> Ouvrir la récupération de mot de passe.', [
                            'for' => 'user_relogin'
                        ]);
                    }, [ 'class' => 'form-group' ]);
        })
                ->token()
                ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'user_register' => 'bool',
            'user_relogin'  => 'bool'
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'user_register' => $validator->getInput('user_register'),
            'user_relogin'  => $validator->getInput('user_relogin')
        ];
    }
}
