<?php

namespace System\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use Soosyze\Components\Http\Redirect;

define("VIEWS_SYSTEM", MODULES_CORE . 'System' . DS . 'Views' . DS);
define("CONFIG_SYSTEM", MODULES_CORE . 'System' . DS . 'Config' . DS);

class System extends \Soosyze\Controller
{
    protected $pathRoutes = CONFIG_SYSTEM . 'routing.json';

    protected $pathServices = CONFIG_SYSTEM . 'service.json';

    public function maintenance()
    {
        return self::template()
                ->setTheme(false)
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-cog" aria-hidden="true"></i> Site en maintenance'
                ])
                ->render('page.content', 'page-maintenance.php', VIEWS_SYSTEM)
                ->withStatus(503);
    }

    public function config()
    {
        $content = self::option()->getOption();

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $optionThemes = [];
        $themes       = self::template()->getThemes();

        foreach ($themes as $theme) {
            $optionThemes[] = $content[ 'theme' ] == $theme
                ? [ 'value' => $theme, 'label' => $theme, 'selected' => 1 ]
                : [ 'value' => $theme, 'label' => $theme ];
        }

        $action = self::router()->getRoute('system.config.check');

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action ]))
            ->group('fieldset-information', 'fieldset', function ($form) use ($content, $optionThemes) {
                $form->legend('legend-information', 'Information')
                ->group('group-email', 'div', function ($form) use ($content) {
                    $form->label('label-email', 'Emai du site', [ 'class' => 'control-label' ])
                    ->email('email', 'email', [
                        'required'    => 'required',
                        'value'       => $content[ 'email' ],
                        'class'       => 'form-control',
                        'placeholder' => 'Email'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('group-maintenance', 'div', function ($form) use ($content) {
                    $form->checkbox('maintenance', 'maintenance', [
                        'checked' => $content[ 'maintenance' ]
                    ])
                    ->label('label-maintenance', '<span class="ui"></span>Mettre le site en maintenance', [
                        'for' => 'maintenance'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('group-theme', 'div', function ($form) use ($optionThemes) {
                    $form->label('label-theme', 'Theme du site', [ 'class' => 'control-label' ])
                    ->select('theme', $optionThemes, [
                        'required' => 'required',
                        'class'    => 'form-control'
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('fieldset-path', 'fieldset', function ($form) use ($content) {
                $form->legend('legend-path', 'Page par défaut')
                ->group('group-pathIndex', 'div', function ($form) use ($content) {
                    $form->label('label-pathIndex', 'Page d’accueil par défaut', [
                        'class' => 'control-label' ])
                    ->text('pathIndex', 'pathIndex', [
                        'required'    => 'required',
                        'value'       => $content[ 'pathIndex' ],
                        'class'       => 'form-control',
                        'placeholder' => 'Path page index'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('group-pathAccessDenied', 'div', function ($form) use ($content) {
                    $form->label('label-pathAccessDenied', 'Page 403 par défaut (accès refusé)', [
                        'class' => 'control-label' ])
                    ->text('pathAccessDenied', 'pathAccessDenied', [
                        'value'       => $content[ 'pathAccessDenied' ],
                        'class'       => 'form-control',
                        'placeholder' => 'Path page access denied'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('group-pathNoFound', 'div', function ($form) use ($content) {
                    $form->label('label-pathNoFound', 'Page 404 par défaut (page non trouvée)', [
                        'class' => 'control-label' ])
                    ->text('pathNoFound', 'pathNoFound', [
                        'value'       => $content[ 'pathNoFound' ],
                        'class'       => 'form-control',
                        'placeholder' => 'Path page not found'
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->group('fieldset-meta', 'fieldset', function ($form) use ($content) {
                $form->legend('legend-meta', 'SEO Metadonnées')
                ->group('group-title', 'div', function ($form) use ($content) {
                    $form->label('label-title', 'Titre du site', [ 'class' => 'control-label' ])
                    ->text('title', 'title', [
                        'value'       => $content[ 'title' ],
                        'class'       => 'form-control',
                        'placeholder' => 'Titre du site',
                        'required'    => 'required'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('group-description', 'div', function ($form) use ($content) {
                    $form->label('label-description', 'Description', [ 'class' => 'control-label' ])
                    ->textarea('description', $content[ 'description' ], [
                        'id'       => 'description',
                        'class'    => 'form-control',
                        'rows'     => 5,
                        'required' => 'required'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('group-keyboard', 'div', function ($form) use ($content) {
                    $form->label('label-keyboard', 'Mots-clés', [ 'class' => 'control-label' ])
                    ->text('keyboard', 'keyboard', [
                        'value'       => $content[ 'keyboard' ],
                        'class'       => 'form-control',
                        'placeholder' => 'Mot1, Mot2, Mot3...'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('group-favicon', 'div', function ($form) use ($content) {
                    $form->label('label-favicon', 'Favicon', [ 'class' => 'control-label' ])
                    ->text('favicon', 'favicon', [
                        'value'       => $content[ 'favicon' ],
                        'class'       => 'form-control',
                        'placeholder' => 'http://mon-site/icon.ico'
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token()
            ->submit('submit', 'Enregistrer');

        if (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
        }
        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ]);
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->setTheme()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-cog" aria-hidden="true"></i> Configuration'
                ])
                ->render('page.content', 'page-configuration.php', VIEWS_SYSTEM, [
                    'form' => $form
        ]);
    }

    public function configCheck($r)
    {
        $post = $r->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email'            => 'required|email|htmlsc',
                'maintenance'      => '!required|bool',
                'theme'            => 'required|inarray:' . implode(',', self::template()->getThemes()),
                'pathIndex'        => 'required|string|htmlsc',
                'pathAccessDenied' => '!required|string|htmlsc',
                'pathNoFound'      => '!required|string|htmlsc',
                'title'            => 'required|string|htmlsc',
                'description'      => 'required|string|max:255|htmlsc',
                'keyboard'         => '!required|string|htmlsc',
                'favicon'          => '!required|url|htmlsc',
                'token'            => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $data = $validator->getInputs();
            /* N'enregistre pas le token de sécurité dans la bdd */
            unset($data[ 'token' ], $data[ 'submit' ]);

            foreach ($data as $key => $value) {
                self::query()
                    ->update('option', [ 'value' => $value ])
                    ->where('name', $key)
                    ->execute();
            }
            $_SESSION[ 'success' ] = [ 'msg' => 'Configuration Enregistré' ];
        } else {
            $_SESSION[ 'inputs' ]      = $validator->getInputs();
            $_SESSION[ 'errors' ]      = $validator->getErrors();
            $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();
        }

        $route = self::router()->getRoute('system.config');

        return new Redirect($route);
    }
}
