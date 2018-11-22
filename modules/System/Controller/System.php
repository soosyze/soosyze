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

    public function configuration()
    {
        $content = self::config()->get('settings');

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $optionThemes = [];
        foreach (self::template()->getThemes() as $theme) {
            $optionThemes[] = [ 'value' => $theme, 'label' => $theme ];
        }

        $action = self::router()->getRoute('system.config.check');

        $form = (new FormBuilder([ 'method' => 'post', 'action' => $action, 'enctype' => 'multipart/form-data' ]))
            ->group('system-information-fieldset', 'fieldset', function ($form) use ($content, $optionThemes) {
                $form->legend('system-information-legend', 'Information')
                ->group('system-email-group', 'div', function ($form) use ($content) {
                    $form->label('system-email-label', 'E-mail du site')
                    ->email('email', 'email', [
                        'class'       => 'form-control',
                        'required'    => 1,
                        'placeholder' => 'E-mail',
                        'value'       => $content[ 'email' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('system-maintenance-group', 'div', function ($form) use ($content) {
                    $form->checkbox('maintenance', 'maintenance', [
                        'checked' => $content[ 'maintenance' ]
                    ])
                    ->label('system-maintenance-group', '<span class="ui"></span>Mettre le site en maintenance', [
                        'for' => 'maintenance'
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('system-theme-group', 'div', function ($form) use ($content, $optionThemes) {
                    $form->label('system-theme-label', 'Theme du site')
                    ->select('theme', 'theme', $optionThemes, [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $content[ 'theme' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
           ->group('system-path-fieldset', 'fieldset', function ($form) use ($content) {
               $form->legend('system-path-legend', 'Page par défaut')
                ->group('system-path_index-group', 'div', function ($form) use ($content) {
                    $form->label('system-path_index-label', 'Page d’accueil par défaut')
                    ->text('path_index', 'path_index', [
                        'class'       => 'form-control',
                        'required'    => 1,
                        'placeholder' => 'Path page index',
                        'value'       => $content[ 'path_index' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('system-path_access_denied-group', 'div', function ($form) use ($content) {
                    $form->label('system-path_access_denied-label', 'Page 403 par défaut (accès refusé)')
                    ->text('path_access_denied', 'path_access_denied', [
                        'class'       => 'form-control',
                        'placeholder' => 'Path page access denied',
                        'value'       => $content[ 'path_access_denied' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('system-path_no_found-group', 'div', function ($form) use ($content) {
                    $form->label('system-path_no_found-label', 'Page 404 par défaut (page non trouvée)')
                    ->text('path_no_found', 'path_no_found', [
                        'class'       => 'form-control',
                        'placeholder' => 'Path page not found',
                        'value'       => $content[ 'path_no_found' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
           })
            ->group('system-metadata-fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('system-metadata-legend', 'SEO Metadonnées')
                ->group('system-title-group', 'div', function ($form) use ($content) {
                    $form->label('system-title-label', 'Titre du site')
                    ->text('title', 'title', [
                        'class'       => 'form-control',
                        'maxlength'   => 64,
                        'placeholder' => 'Titre du site',
                        'required'    => 'required',
                        'value'       => $content[ 'title' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('system-description-group', 'div', function ($form) use ($content) {
                    $form->label('system-description-label', 'Description')
                    ->textarea('description', 'description', $content[ 'description' ], [
                        'class'     => 'form-control',
                        'maxlength' => 256,
                        'required'  => 'required',
                        'rows'      => 5
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('system-keyboard-group', 'div', function ($form) use ($content) {
                    $form->label('system-keyboard-label', 'Mots-clés')
                    ->text('keyboard', 'keyboard', [
                        'class'       => 'form-control',
                        'placeholder' => 'Mot1, Mot2, Mot3...',
                        'value'       => $content[ 'keyboard' ]
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

        if (isset($_SESSION[ 'errors' ])) {
            $form->addErrors($_SESSION[ 'errors' ])
                ->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors' ], $_SESSION[ 'errors_keys' ]);
        } elseif (isset($_SESSION[ 'success' ])) {
            $form->setSuccess($_SESSION[ 'success' ]);
            unset($_SESSION[ 'success' ], $_SESSION[ 'errors' ]);
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

    public function configCheck($req)
    {
        $post = $req->getParsedBody();

        $validator = (new Validator())
            ->setRules([
                'email'              => 'required|email|max:254|htmlsc',
                'maintenance'        => '!required|bool',
                'theme'              => 'required|inarray:' . implode(',', self::template()->getThemes()),
                'path_index'         => 'required|string|htmlsc',
                'path_access_denied' => '!required|string|htmlsc',
                'path_no_found'      => '!required|string|htmlsc',
                'title'              => 'required|string|max:64|htmlsc',
                'description'        => 'required|string|max:256|htmlsc',
                'keyboard'           => '!required|string|htmlsc',
                'favicon'            => '!required|url|htmlsc',
                'token'              => 'required|token'
            ])
            ->setInputs($post);

        if ($validator->isValid()) {
            $data = $validator->getInputs();
            /* N'enregistre pas le token de sécurité dans la bdd */
            unset($data[ 'token' ], $data[ 'submit' ]);

            foreach ($data as $key => $value) {
                self::config()->set('settings.' . $key, $value);
            }
            $_SESSION[ 'success' ] = [ 'Configuration Enregistrée' ];
        } else {
            $_SESSION[ 'inputs' ]      = $validator->getInputs();
            $_SESSION[ 'errors' ]      = $validator->getErrors();
            $_SESSION[ 'errors_keys' ] = $validator->getKeyUniqueErrors();
        }

        $route = self::router()->getRoute('system.config');

        return new Redirect($route);
    }
}
