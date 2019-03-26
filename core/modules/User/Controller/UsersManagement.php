<?php

namespace User\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

class UsersManagement extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes = CONFIG_USER . 'routing-user_management.json';
    }

    public function admin()
    {
        $users = self::user()->getUsers();
        foreach ($users as &$user) {
            $user[ 'link_show' ]   = self::router()->getRoute('user.show', [
                ':id' => $user[ 'user_id' ]
            ]);
            $user[ 'link_edit' ]   = self::router()->getRoute('user.edit', [
                ':id' => $user[ 'user_id' ]
            ]);
            $user[ 'link_remove' ] = self::router()->getRoute('user.remove', [
                ':id' => $user[ 'user_id' ]
            ]);
            $user[ 'roles' ]       = self::user()->getRolesUser($user[ 'user_id' ]);
        }

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => 'Administrer les utilisateurs'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-user_management.php', VIEWS_USER, [
                    'users'              => $users,
                    'link_add'           => self::router()->getRoute('user.create'),
                    'link_role'          => self::router()->getRoute('user.role.admin'),
                    'link_permission'    => self::router()->getRoute('user.permission.admin'),
                    'link_config'        => self::router()->getRoute('user.management.config'),
                    'granted_permission' => self::user()->isGranted('user.permission.manage'),
        ]);
    }

    public function configuration()
    {
        $content = [
            'user_register' => self::config()->get('settings.user_register'),
            'user_relogin'  => self::config()->get('settings.user_relogin')
        ];
        $form    = (new FormBuilder([ 'method' => 'post', 'action' => self::router()->getRoute('user.management.config.check') ]))
            ->group('config-inscription-fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('config-inscription-legend', 'Inscription')
                ->group('config-register-group', 'div', function ($form) use ($content) {
                    $form->checkbox('user_register', 'user_register', [ 'checked' => $content[ 'user_register' ] ])
                    ->label('config-register-label', '<span class="ui"></span> Ouvrir l\'inscription.', [
                        'for' => 'user_register'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('config-relogin-group', 'div', function ($form) use ($content) {
                    $form->checkbox('user_relogin', 'user_relogin', [ 'checked' => $content[ 'user_relogin' ] ])
                    ->label('config-relogin-label', '<span class="ui"></span> Ouvrir la récupération de mot de passe.', [
                        'for' => 'user_relogin'
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()->getTheme('theme_admin')
                ->view('page', [ 'title_main' => 'Configuration' ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-user-config.php', VIEWS_USER, [ 'form' => $form ]);
    }

    public function configurationCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'user_register' => 'bool',
                'user_relogin'  => 'bool'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $data = [
                'user_register' => $validator->getInput('user_register'),
                'user_relogin'  => $validator->getInput('user_relogin')
            ];

            foreach ($data as $key => $value) {
                self::config()->set('settings.' . $key, $value);
            }
            $_SESSION[ 'messages' ][ 'success' ] = [ 'Configuration Enregistrée' ];
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }
        $route = self::router()->getRoute('user.management.config');

        return new Redirect($route);
    }
}
