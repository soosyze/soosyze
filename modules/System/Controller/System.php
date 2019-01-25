<?php

namespace System\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

define('VIEWS_SYSTEM', MODULES_CORE . 'System' . DS . 'Views' . DS);
define('CONFIG_SYSTEM', MODULES_CORE . 'System' . DS . 'Config' . DS);

class System extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = CONFIG_SYSTEM . 'service.json';
        $this->pathRoutes   = CONFIG_SYSTEM . 'routing.json';
    }

    public function maintenance()
    {
        return self::template()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-cog" aria-hidden="true"></i> Site en maintenance'
                ])
                ->render('page.content', 'page-maintenance.php', VIEWS_SYSTEM)
                ->withStatus(503);
    }

    public function edit()
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

        $action = self::router()->getRoute('system.config.update');

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
                }, [ 'class' => 'form-group' ])
                ->group('system-theme_admin-group', 'div', function ($form) use ($content, $optionThemes) {
                    $form->label('system-theme_admin-label', 'Theme d\'administration du site')
                    ->select('theme_admin', 'theme_admin', $optionThemes, [
                        'class'    => 'form-control',
                        'required' => 1,
                        'selected' => $content[ 'theme_admin' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('system-logo-group', 'div', function ($form) use ($content) {
                    $form->label('label-logo', 'Logo', [ 'class' => 'control-label' ]);
                    self::file()->formFile('logo', $form, $content[ 'logo' ]);
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
                ->group('system-group-favicon', 'div', function ($form) use ($content) {
                    $form->label('system-favicon-label', 'Favicon', [ 'class' => 'control-label' ]);
                    self::file()->formFile('favicon', $form, $content[ 'favicon' ]);
                    $form->html('system-favicon-info-size', '<p:css:attr>:_content</p>', [
                        '_content' => 'Le fichier doit peser moins de <b>200 Ko</b>.'
                    ])->html('system-favicon-info-ext', '<p:css:attr>:_content</p>', [
                        '_content' => 'Les Extensions autorisées sont <b>png ico</b>.'
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
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-cog" aria-hidden="true"></i> Configuration'
                ])
                ->render('page.content', 'page-configuration.php', VIEWS_SYSTEM, [
                    'form' => $form
        ]);
    }

    public function update($req)
    {
        $post  = $req->getParsedBody();
        $files = $req->getUploadedFiles();

        $validator = (new Validator())
            ->setRules([
                'email'              => 'required|email|max:254|htmlsc',
                'maintenance'        => '!required|bool',
                'theme'              => 'required|inarray:' . implode(',', self::template()->getThemes()),
                'theme_admin'        => 'required|inarray:' . implode(',', self::template()->getThemes()),
                'path_index'         => 'required|string|htmlsc',
                'path_access_denied' => '!required|string|htmlsc',
                'path_no_found'      => '!required|string|htmlsc',
                'title'              => 'required|string|max:64|htmlsc',
                'description'        => 'required|string|max:256|htmlsc',
                'keyboard'           => '!required|string|htmlsc',
                'favicon'            => '!required|file_mimes:png,ico|image_dimensions_height:16,310|image_dimensions_width:16,310|max:100000',
                'logo'               => '!required|image|max:2000000',
                'token'              => 'required|token'
            ])
            ->setInputs($post + $files);

        $this->container->callHook('system.update.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = [
                'email'              => $validator->getInput('email'),
                'maintenance'        => (bool) $validator->getInput('maintenance'),
                'theme'              => $validator->getInput('theme'),
                'theme_admin'        => $validator->getInput('theme_admin'),
                'path_index'         => $validator->getInput('path_index'),
                'path_access_denied' => $validator->getInput('path_access_denied'),
                'path_no_found'      => $validator->getInput('path_no_found'),
                'title'              => $validator->getInput('title'),
                'description'        => $validator->getInput('description'),
                'keyboard'           => $validator->getInput('keyboard'),
            ];

            foreach ($data as $key => $value) {
                self::config()->set('settings.' . $key, $value);
            }
            $this->saveFile('favicon', $validator);
            $this->saveFile('logo', $validator);
            $_SESSION[ 'success' ] = [ 'Configuration Enregistrée' ];
        } else {
            $server = $req->getServerParams();
            if (empty($post) && empty($files) && $server[ 'CONTENT_LENGTH' ] > 0) {
                $_SESSION[ 'errors' ]      = [ 'La quantité totales des données reçues '
                    . 'dépasse la valeur maximale autorisée par la directive post_max_size '
                    . 'de votre fichier php.ini' ];
                $_SESSION[ 'errors_keys' ] = [];
            } else {
                $_SESSION[ 'inputs' ]      = $validator->getInputsWithout('favicon', 'logo');
                $_SESSION[ 'errors' ]      = $validator->getErrors();
                $_SESSION[ 'errors_keys' ] = $validator->getKeyInputErrors();
            }
        }

        $route = self::router()->getRoute('system.config.edit');

        return new Redirect($route);
    }

    private function saveFile($key, $validator)
    {
        if (!($validator->getInput($key) instanceof \Psr\Http\Message\UploadedFileInterface)) {
            return;
        }
        if ($validator->getInput($key)->getError() === UPLOAD_ERR_OK) {
            $path    = self::core()->getSetting('files_public', 'app/files');
            $favicon = $validator->getInput($key)->getClientFilename();
            $ext     = \Soosyze\Components\Util\Util::getFileExtension($favicon);

            $move = $path . "/$key." . $ext;
            $validator->getInput($key)->moveTo($move);
            self::config()->set("settings.$key", $move);
        } elseif ($validator->getInput($key)->getError() === UPLOAD_ERR_NO_FILE) {
            $name    = $validator->getInput("file-name-$key");
            $favicon = self::config()->get("settings.$key");
            if (empty($name) && $favicon) {
                self::config()->set("settings.$key", '');
                unlink($favicon);
            }
        }
    }
}
