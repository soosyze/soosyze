<?php

namespace SoosyzeCore\System\Services;

final class HookConfig implements \SoosyzeCore\Config\Services\ConfigInterface
{
    protected $file;

    protected $router;

    protected $template;

    protected $translate;

    public function __construct($file, $router, $template, $translate)
    {
        $this->file      = $file;
        $this->router    = $router;
        $this->template  = $template;
        $this->translate = $translate;
    }

    public function menu(&$menu)
    {
        $menu[ 'system' ] = [
            'title_link' => 'System'
        ];
    }

    public function form(&$form, $data, $req)
    {
        $optionThemes      = [];
        $optionThemesAdmin = [];

        $composers = $this->template->getThemes();

        foreach ($composers as $key => $composer) {
            $theme = [
                'value' => $key,
                'label' => $key
            ];
            if (empty($composer[ 'extra' ][ 'soosyze-theme' ][ 'options' ][ 'admin' ])) {
                $optionThemes[] = $theme;
            } else {
                $optionThemesAdmin[] = $theme;
            }
        }

        $optionTimezone = [];
        foreach (timezone_identifiers_list() as $value) {
            $optionTimezone[] = [ 'value' => $value, 'label' => $value ];
        }

        $optionLang   = $this->translate->getLang();
        $optionLang[] = [ 'value' => 'en', 'label' => 'English' ];

        $form->group('translate-fieldset', 'fieldset', function ($form) use ($data, $optionLang, $optionTimezone) {
            $form->legend('translate-legend', t('Language'))
                    ->group('lang-group', 'div', function ($form) use ($data, $optionLang) {
                        $form->label('lang-label', t('Language'))
                        ->select('lang', $optionLang, [
                            'class'    => 'form-control',
                            'required' => 1,
                            'selected' => $data[ 'lang' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('timezone-group', 'div', function ($form) use ($data, $optionTimezone) {
                        $form->label('timezone-label', t('Timezone'))
                        ->select('timezone', $optionTimezone, [
                            'class'    => 'form-control',
                            'required' => 1,
                            'selected' => $data[ 'timezone' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
        })
                ->group('information-fieldset', 'fieldset', function ($form) use ($data, $optionThemes, $optionThemesAdmin) {
                    $form->legend('information-legend', t('Information'))
                    ->group('email-group', 'div', function ($form) use ($data) {
                        $form->label('email-label', t('E-mail of the site'), [
                            'data-tooltip' => t('E-mail used for the general configuration, for your contacts, the recovery of your password ...')
                        ])
                        ->email('email', [
                            'class'       => 'form-control',
                            'required'    => 1,
                            'placeholder' => t('E-mail'),
                            'value'       => $data[ 'email' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('maintenance-group', 'div', function ($form) use ($data) {
                        $form->checkbox('maintenance', [
                            'checked' => $data[ 'maintenance' ]
                        ])
                        ->label('maintenance-label', '<i class="ui" aria-hidden="true"></i> ' . t('Put the site in maintenance'), [
                            'for' => 'maintenance'
                        ]);
                    }, [ 'class' => 'form-group' ]);
                    if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
                        $form->group('rewrite_engine-group', 'div', function ($form) use ($data) {
                            $form->checkbox('rewrite_engine', [
                                'checked' => $data[ 'rewrite_engine' ]
                            ])
                            ->label('rewrite_engine-label', '<i class="ui" aria-hidden="true"></i> ' . t('Make the URLs clean'), [
                                'for' => 'rewrite_engine'
                            ]);
                        }, [ 'class' => 'form-group' ]);
                    }
                    $form->group('theme-group', 'div', function ($form) use ($data, $optionThemes) {
                        $form->label('theme-label', t('Website theme'))
                        ->select('theme', $optionThemes, [
                            'class'    => 'form-control',
                            'required' => 1,
                            'selected' => $data[ 'theme' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('theme_admin-group', 'div', function ($form) use ($data, $optionThemesAdmin) {
                        $form->label('theme_admin-label', t('Website administration theme'))
                        ->select('theme_admin', $optionThemesAdmin, [
                            'class'    => 'form-control',
                            'required' => 1,
                            'selected' => $data[ 'theme_admin' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('theme_admin_dark-group', 'div', function ($form) use ($data) {
                        $form->checkbox('theme_admin_dark', [
                            'checked' => $data[ 'theme_admin_dark' ]
                        ])
                        ->label('theme_admin_dark-label', '<i class="ui" aria-hidden="true"></i> ' . t('Activate the dark mode for the administrator theme if available'), [
                            'for' => 'theme_admin_dark'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('logo-group', 'div', function ($form) use ($data) {
                        $form->label('logo-label', t('Logo'), [
                            'class'        => 'control-label',
                            'data-tooltip' => '200ko maximum.',
                            'for'          => 'logo'
                        ]);
                        $this->file->inputFile('logo', $form, $data[ 'logo' ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('path-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('path-legend', t('Default page'))
                    ->group('path_index-group', 'div', function ($form) use ($data) {
                        $form->label('path_index-label', t('Default homepage'), [
                            'data-tooltip' => t('Content link displayed on your site\'s homepage.'),
                            'for'          => 'path_index',
                            'required'     => true
                        ])
                        ->group('path_index-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:attr>:_content</span>', [
                                '_content' => $this->router->makeRoute(''),
                                'id'       => ''
                            ])
                            ->text('path_index', [
                                'class'       => 'form-control',
                                'required'    => 1,
                                'placeholder' => t('Example: node/1'),
                                'value'       => $data[ 'path_index' ]
                            ]);
                        }, [ 'class' => 'form-group-flex' ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('path_access_denied-group', 'div', function ($form) use ($data) {
                        $form->label('path_access_denied-label', t('Page 403 by default (access denied)'), [
                            'data-tooltip' => t('The content of the link is displayed if a user accesses a forbidden page.'),
                            'for'          => 'path_access_denied'
                        ])
                        ->group('path_access_denied-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:attr>:_content</span>', [
                                '_content' => $this->router->makeRoute(''),
                                'id'       => ''
                            ])
                            ->text('path_access_denied', [
                                'class'       => 'form-control',
                                'placeholder' => t('Example: user/login'),
                                'value'       => $data[ 'path_access_denied' ]
                            ]);
                        }, [ 'class' => 'form-group-flex' ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('path_no_found-group', 'div', function ($form) use ($data) {
                        $form->label('path_no_found-label', t('Page 404 by default (page not found)'), [
                            'data-tooltip' => t('The content of the link is displayed if a user accesses a non-existent page.'),
                            'for'          => 'path_no_found'
                        ])
                        ->group('path_no_found-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:attr>:_content</span>', [
                                '_content' => $this->router->makeRoute(''),
                                'id'       => ''
                            ])
                            ->text('path_no_found', [
                                'class'       => 'form-control',
                                'placeholder' => t('Example: node/1'),
                                'value'       => $data[ 'path_no_found' ]
                            ]);
                        }, [ 'class' => 'form-group-flex' ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('path_maintenance-group', 'div', function ($form) use ($data) {
                        $form->label('path_maintenance-label', t('Default maintenance page'), [
                            'data-tooltip' => t('Leave blank to use your theme\'s default page-maintenance.php template'),
                            'for'          => 'path_maintenance'
                        ])
                        ->group('path_maintenance-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path_maintenance', '<span:attr>:_content</span>', [
                                '_content' => $this->router->makeRoute(''),
                            ])
                            ->text('path_maintenance', [
                                'class'       => 'form-control',
                                'placeholder' => t('Example: node/1'),
                                'value'       => $data[ 'path_maintenance' ]
                            ]);
                        }, [ 'class' => 'form-group-flex' ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('metadata-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('metadata-legend', t('SEO'))
                    ->group('meta_title-group', 'div', function ($form) use ($data) {
                        $form->label('meta_title-label', t('Website title'), [
                            'data-tooltip' => t('The main title of your site also appears in the title of your browser window.')
                        ])
                        ->text('meta_title', [
                            'class'     => 'form-control',
                            'maxlength' => 64,
                            'required'  => 'required',
                            'value'     => $data[ 'meta_title' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('meta_description-group', 'div', function ($form) use ($data) {
                        $form->label('meta_description-label', t('Description'), [
                            'data-tooltip' => t('Help your SEO and appears in the search engines.')
                        ])
                        ->textarea('meta_description', $data[ 'meta_description' ], [
                            'class'     => 'form-control',
                            'maxlength' => 256,
                            'required'  => 'required',
                            'rows'      => 5
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('meta_keyboard-group', 'div', function ($form) use ($data) {
                        $form->label('meta_keyboard-label', t('Keywords'))
                        ->text('meta_keyboard', [
                            'class'       => 'form-control',
                            'placeholder' => t('Word1, Word2, Word3 ...'),
                            'value'       => $data[ 'meta_keyboard' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('group-favicon', 'div', function ($form) use ($data) {
                        $form->label('favicon-label', t('Favicon'), [
                            'class'        => 'control-label',
                            'data-tooltip' => t('Image to the left of the title of your browser window.'),
                            'for'          => 'favicon'
                        ]);
                        $this->file->inputFile('favicon', $form, $data[ 'favicon' ]);
                        $form->html('favicon-info-size', '<p:attr>:_content</p>', [
                            '_content' => t('The file must weigh less than 100 KB.')
                        ])->html('favicon-info-dimensions', '<p:attr>:_content</p>', [
                            '_content' => t('The width and height min and max: 16px and 310px.')
                        ]);
                    }, [ 'class' => 'form-group' ]);
                });
    }

    public function validator(&$validator)
    {
        $langs  = implode(',', array_keys($this->translate->getLang())) . ',en';
        $validator->setRules([
            'lang'               => 'required|inarray:' . $langs,
            'timezone'           => 'required|timezone',
            'email'              => 'required|email|max:254|to_htmlsc',
            'maintenance'        => '!required|bool',
            'rewrite_engine'     => 'bool',
            'theme'              => 'required|string',
            'theme_admin'        => 'required|string',
            'theme_admin_dark'   => 'bool',
            'logo'               => '!required|image|max:200Kb',
            'path_index'         => 'route',
            'path_access_denied' => '!required|route',
            'path_no_found'      => '!required|route',
            'path_maintenance'   => '!required|route',
            'meta_title'         => 'required|string|max:64|to_htmlsc',
            'meta_description'   => 'required|string|max:256|to_htmlsc',
            'meta_keyboard'      => '!required|string|to_htmlsc',
            'favicon'            => '!required|image:png,ico|image_dimensions_height:16,310|image_dimensions_width:16,310|max:100Kb'
        ])->setLabel([
            'lang'               => t('Language'),
            'timezone'           => t('Timezone'),
            'email'              => t('E-mail of the site'),
            'maintenance'        => t('Put the site in maintenance'),
            'rewrite_engine'     => t('Make the URLs clean'),
            'theme'              => t('Website theme'),
            'theme_admin'        => t('Website administration theme'),
            'logo'               => t('Logo'),
            'path_index'         => t('Default page'),
            'path_access_denied' => t('Page 403 by default (access denied)'),
            'path_no_found'      => t('Page 404 by default (page not found)'),
            'path_maintenance'   => t('Page de maintenance par défaut'),
            'meta_title'         => t('Website title'),
            'meta_description'   => t('Description'),
            'meta_keyboard'      => t('Keywords'),
            'favicon'            => t('Favicon')
        ]);
    }

    public function before(&$validator, &$data, $id)
    {
        $data = [
            'lang'               => $validator->getInput('lang'),
            'timezone'           => $validator->getInput('timezone'),
            'email'              => $validator->getInput('email'),
            'maintenance'        => (bool) $validator->getInput('maintenance'),
            'rewrite_engine'     => (bool) $validator->getInput('rewrite_engine'),
            'theme'              => $validator->getInput('theme'),
            'theme_admin'        => $validator->getInput('theme_admin'),
            'theme_admin_dark'   => (bool) $validator->getInput('theme_admin_dark'),
            'path_index'         => $validator->getInput('path_index'),
            'path_access_denied' => $validator->getInput('path_access_denied'),
            'path_no_found'      => $validator->getInput('path_no_found'),
            'path_maintenance'   => $validator->getInput('path_maintenance'),
            'meta_title'         => $validator->getInput('meta_title'),
            'meta_description'   => $validator->getInput('meta_description'),
            'meta_keyboard'      => $validator->getInput('meta_keyboard'),
        ];
    }

    public function files(&$inputsFile)
    {
        $inputsFile = [ 'logo', 'favicon' ];
    }

    public function after(&$validator, $data, $id)
    {
    }
}
