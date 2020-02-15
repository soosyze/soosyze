<?php

namespace SoosyzeCore\System\Services;

class HookConfig
{
    protected $template;

    protected $file;

    protected $translate;

    protected $router;

    public function __construct($router, $template, $file, $translate)
    {
        $this->router    = $router;
        $this->template  = $template;
        $this->file      = $file;
        $this->translate = $translate;
    }

    public function menu(&$menu)
    {
        $menu[ 'system' ] = [
            'title_link' => 'System'
        ];
    }

    public function form(&$form, $data)
    {
        $optionThemes = [];
        foreach ($this->template->getThemes() as $theme) {
            $optionThemes[] = [ 'value' => $theme, 'label' => $theme ];
        }

        $optionLang   = $this->translate->getLang();
        $optionLang[] = [ 'value' => 'en', 'label' => 'English' ];

        return $form->group('system-translate-fieldset', 'fieldset', function ($form) use ($data, $optionLang) {
            $form->legend('system-translate-legend', t('Translation'))
                    ->group('system-translate-group', 'div', function ($form) use ($data, $optionLang) {
                        $form->label('system-translate-label', t('Language'))
                        ->select('lang', $optionLang, [
                            'class'    => 'form-control',
                            'selected' => $data[ 'lang' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
        })
                ->group('system-information-fieldset', 'fieldset', function ($form) use ($data, $optionThemes) {
                    $form->legend('system-information-legend', t('Information'))
                    ->group('system-email-group', 'div', function ($form) use ($data) {
                        $form->label('system-email-label', t('E-mail of the site'), [
                            'data-tooltip' => t('E-mail used for the general configuration, for your contacts, the recovery of your password ...')
                        ])
                        ->email('email', [
                            'class'       => 'form-control',
                            'required'    => 1,
                            'placeholder' => t('E-mail'),
                            'value'       => $data[ 'email' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-maintenance-group', 'div', function ($form) use ($data) {
                        $form->checkbox('maintenance', [
                            'checked' => $data[ 'maintenance' ]
                        ])
                        ->label('system-maintenance-group', '<i class="ui" aria-hidden="true"></i> ' . t('Put the site in maintenance'), [
                            'for' => 'maintenance'
                        ]);
                    }, [ 'class' => 'form-group' ]);
                    if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
                        $form->group('system-rewrite_engine-group', 'div', function ($form) use ($data) {
                            $form->checkbox('rewrite_engine', [
                                'checked' => $data[ 'rewrite_engine' ]
                            ])
                            ->label('system-maintenance-group', '<i class="ui" aria-hidden="true"></i> ' . t('Make the URLs clean'), [
                                'for' => 'rewrite_engine'
                            ]);
                        }, [ 'class' => 'form-group' ]);
                    }
                    $form->group('system-theme-group', 'div', function ($form) use ($data, $optionThemes) {
                        $form->label('system-theme-label', t('Website theme'))
                        ->select('theme', $optionThemes, [
                            'class'    => 'form-control',
                            'selected' => $data[ 'theme' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-theme_admin-group', 'div', function ($form) use ($data, $optionThemes) {
                        $form->label('system-theme_admin-label', t('Website administration theme'))
                        ->select('theme_admin', $optionThemes, [
                            'class'    => 'form-control',
                            'selected' => $data[ 'theme_admin' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-logo-group', 'div', function ($form) use ($data) {
                        $form->label('label-logo', t('Logo'), [
                            'class'        => 'control-label',
                            'data-tooltip' => '200ko maximum.',
                            'for'          => 'logo'
                        ]);
                        $this->file->inputFile('logo', $form, $data[ 'logo' ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('system-path-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('system-path-legend', t('Default page'))
                    ->group('system-path_index-group', 'div', function ($form) use ($data) {
                        $form->label('system-path_index-label', t('Default homepage'), [
                            'data-tooltip' => t('Content link displayed on your site\'s homepage.'),
                            'for'          => 'path_index'
                        ])
                        ->group('system-path_index-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:css:attr>:_content</span>', [
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
                    ->group('system-path_access_denied-group', 'div', function ($form) use ($data) {
                        $form->label('system-path_access_denied-label', t('Page 403 by default (access denied)'), [
                            'data-tooltip' => t('The content of the link is displayed if a user accesses a forbidden page.'),
                            'for'          => 'path_access_denied'
                        ])
                        ->group('system-path_access_denied-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:css:attr>:_content</span>', [
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
                    ->group('system-path_no_found-group', 'div', function ($form) use ($data) {
                        $form->label('system-path_no_found-label', t('Page 404 by default (page not found)'), [
                            'data-tooltip' => t('The content of the link is displayed if a user accesses a non-existent page.'),
                            'for'          => 'path_no_found'
                        ])
                        ->group('system-path_no_found-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:css:attr>:_content</span>', [
                                '_content' => $this->router->makeRoute(''),
                                'id'       => ''
                            ])
                            ->text('path_no_found', [
                                'class'       => 'form-control',
                                'placeholder' => t('Example: node/1'),
                                'value'       => $data[ 'path_no_found' ]
                            ]);
                        }, [ 'class' => 'form-group-flex' ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('system-metadata-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('system-metadata-legend', t('SEO'))
                    ->group('system-meta_title-group', 'div', function ($form) use ($data) {
                        $form->label('system-meta_title-label', t('Website title'), [
                            'data-tooltip' => t('The main title of your site also appears in the title of your browser window.')
                        ])
                        ->text('meta_title', [
                            'class'     => 'form-control',
                            'maxlength' => 64,
                            'required'  => 'required',
                            'value'     => $data[ 'meta_title' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-meta_description-group', 'div', function ($form) use ($data) {
                        $form->label('system-meta_description-label', t('Description'), [
                            'data-tooltip' => t('Help your SEO and appears in the search engines.')
                        ])
                        ->textarea('meta_description', $data[ 'meta_description' ], [
                            'class'     => 'form-control',
                            'maxlength' => 256,
                            'required'  => 'required',
                            'rows'      => 5
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-meta_keyboard-group', 'div', function ($form) use ($data) {
                        $form->label('system-meta_keyboard-label', t('Keywords'))
                        ->text('meta_keyboard', [
                            'class'       => 'form-control',
                            'placeholder' => t('Word1, Word2, Word3 ...'),
                            'value'       => $data[ 'meta_keyboard' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-group-favicon', 'div', function ($form) use ($data) {
                        $form->label('system-favicon-label', t('Favicon'), [
                            'class'        => 'control-label',
                            'data-tooltip' => t('Image to the left of the title of your browser window.'),
                            'for'          => 'favicon'
                        ]);
                        $this->file->inputFile('favicon', $form, $data[ 'favicon' ]);
                        $form->html('system-favicon-info-size', '<p:css:attr>:_content</p>', [
                            '_content' => t('The file must weigh less than 100 KB.')
                        ])->html('system-favicon-info-dimensions', '<p:css:attr>:_content</p>', [
                            '_content' => t('The width and height min and max: 16px and 310px.')
                        ]);
                    }, [ 'class' => 'form-group' ]);
                });
    }

    public function validator(&$validator)
    {
        $themes = implode(',', $this->template->getThemes());
        $langs  = implode(',', array_keys($this->translate->getLang())) . ',en';
        $validator->setRules([
            'lang'                => 'required|inarray:' . $langs,
            'email'               => 'required|email|max:254|htmlsc',
            'maintenance'         => '!required|bool',
            'rewrite_engine'      => 'bool',
            'theme'               => 'required|inarray:' . $themes,
            'theme_admin'         => 'required|inarray:' . $themes,
            'logo'                => '!required|image|max:200Kb',
            'path_index'          => 'route',
            'path_access_denied'  => '!required|route',
            'path_no_found'       => '!required|route',
            'meta_title'          => 'required|string|max:64|htmlsc',
            'meta_description'    => 'required|string|max:256|htmlsc',
            'meta_keyboard'       => '!required|string|htmlsc',
            'favicon'             => '!required|image:png,ico|image_dimensions_height:16,310|image_dimensions_width:16,310|max:100Kb'
        ])->setLabel([
            'lang'               => t('Language'),
            'email'              => t('E-mail of the site'),
            'maintenance'        => t('Put the site in maintenance'),
            'rewrite_engine'     => t('Make the URLs clean'),
            'theme'              => t('Website theme'),
            'theme_admin'        => t('Website administration theme'),
            'logo'               => t('Logo'),
            'path_index'         => t('Default page'),
            'path_access_denied' => t('Page 403 by default (access denied)'),
            'path_no_found'      => t('Page 404 by default (page not found)'),
            'meta_title'         => t('Website title'),
            'meta_description'   => t('Description'),
            'meta_keyboard'      => t('Keywords'),
            'favicon'            => t('Favicon')
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'lang'               => $validator->getInput('lang'),
            'email'              => $validator->getInput('email'),
            'maintenance'        => (bool) $validator->getInput('maintenance'),
            'rewrite_engine'     => (bool) $validator->getInput('rewrite_engine'),
            'theme'              => $validator->getInput('theme'),
            'theme_admin'        => $validator->getInput('theme_admin'),
            'path_index'         => $validator->getInput('path_index'),
            'path_access_denied' => $validator->getInput('path_access_denied'),
            'path_no_found'      => $validator->getInput('path_no_found'),
            'meta_title'         => $validator->getInput('meta_title'),
            'meta_description'   => $validator->getInput('meta_description'),
            'meta_keyboard'      => $validator->getInput('meta_keyboard'),
        ];
    }

    public function files(&$inputFiles)
    {
        $inputFiles = [ 'logo', 'favicon' ];
    }
}
