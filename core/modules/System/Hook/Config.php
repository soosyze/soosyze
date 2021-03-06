<?php

namespace SoosyzeCore\System\Hook;

final class Config implements \SoosyzeCore\Config\ConfigInterface
{
    private static $attrGrp = [ 'class' => 'form-group' ];

    /**
     * @var \SoosyzeCore\Filesystem\Services\File
     */
    private $file;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    /**
     * @var \SoosyzeCore\Translate\Services\Translation
     */
    private $translate;

    public function __construct($file, $router, $translate)
    {
        $this->file      = $file;
        $this->router    = $router;
        $this->translate = $translate;
    }

    public function defaultValues()
    {
        return [
            'lang'               => '',
            'timezone'           => '',
            'maintenance'        => '',
            'rewrite_engine'     => '',
            'path_index'         => '',
            'path_access_denied' => '',
            'path_no_found'      => '',
            'path_maintenance'   => '',
            'meta_title'         => '',
            'meta_description'   => '',
            'meta_keyboard'      => ''
        ];
    }

    public function menu(array &$menu)
    {
        $menu[ 'system' ] = [
            'title_link' => 'System'
        ];
    }

    public function form(&$form, array $data, $req)
    {
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
                            'class'     => 'form-control',
                            'required'  => 1,
                            ':selected' => $data[ 'lang' ]
                        ]);
                    }, self::$attrGrp)
                    ->group('timezone-group', 'div', function ($form) use ($data, $optionTimezone) {
                        $form->label('timezone-label', t('Timezone'))
                        ->select('timezone', $optionTimezone, [
                            'class'     => 'form-control',
                            'required'  => 1,
                            ':selected' => $data[ 'timezone' ]
                        ]);
                    }, self::$attrGrp);
        })
                ->group('information-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('information-legend', t('Information'))
                    ->group('maintenance-group', 'div', function ($form) use ($data) {
                        $form->checkbox('maintenance', [
                            'checked' => $data[ 'maintenance' ]
                        ])
                        ->label('maintenance-label', '<i class="ui" aria-hidden="true"></i> ' . t('Put the site in maintenance'), [
                            'for' => 'maintenance'
                        ]);
                    }, self::$attrGrp)
                    ->group('rewrite_engine-group', 'div', function ($form) use ($data) {
                        $isModeRewrite = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());

                        $form->checkbox('rewrite_engine', [
                            'checked'  => $data[ 'rewrite_engine' ],
                            'disabled' => !$isModeRewrite
                        ])
                        ->label('rewrite_engine-label', '<i class="ui" aria-hidden="true"></i> ' . t('Make the URLs clean'), [
                                'for' => 'rewrite_engine'
                        ]);
                        if (!$isModeRewrite) {
                            $form->html('rewrite_engine-info', '<p:attr>:content</p>', [
                                ':content' => t('Your server does not determine whether clean URLs can be enabled.'),
                                'style'    => 'color: red;'
                            ]);
                        }
                    }, self::$attrGrp);
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
                            $form->html('base_path', '<span:attr>:content</span>', [
                                ':content' => $this->router->makeRoute('')
                            ])
                            ->text('path_index', [
                                'class'       => 'form-control',
                                'data-link'   => $this->router->getRoute('api.route'),
                                'required'    => 1,
                                'placeholder' => t('Example: node/1'),
                                'value'       => $data[ 'path_index' ]
                            ]);
                        }, [ 'class' => 'form-group-flex api_route' ]);
                    }, self::$attrGrp)
                    ->group('path_access_denied-group', 'div', function ($form) use ($data) {
                        $form->label('path_access_denied-label', t('Page 403 by default (access denied)'), [
                            'data-tooltip' => t('The content of the link is displayed if a user accesses a forbidden page.'),
                            'for'          => 'path_access_denied'
                        ])
                        ->group('path_access_denied-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:attr>:content</span>', [
                                ':content' => $this->router->makeRoute('')
                            ])
                            ->text('path_access_denied', [
                                'class'       => 'form-control',
                                'data-link'   => $this->router->getRoute('api.route'),
                                'placeholder' => t('Example: user/login'),
                                'value'       => $data[ 'path_access_denied' ]
                            ]);
                        }, [ 'class' => 'form-group-flex api_route' ]);
                    }, self::$attrGrp)
                    ->group('path_no_found-group', 'div', function ($form) use ($data) {
                        $form->label('path_no_found-label', t('Page 404 by default (page not found)'), [
                            'data-tooltip' => t('The content of the link is displayed if a user accesses a non-existent page.'),
                            'for'          => 'path_no_found'
                        ])
                        ->group('path_no_found-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path', '<span:attr>:content</span>', [
                                ':content' => $this->router->makeRoute('')
                            ])
                            ->text('path_no_found', [
                                'class'       => 'form-control',
                                'data-link'   => $this->router->getRoute('api.route'),
                                'placeholder' => t('Example: node/1'),
                                'value'       => $data[ 'path_no_found' ]
                            ]);
                        }, [ 'class' => 'form-group-flex api_route' ]);
                    }, self::$attrGrp)
                    ->group('path_maintenance-group', 'div', function ($form) use ($data) {
                        $form->label('path_maintenance-label', t('Default maintenance page'), [
                            'data-tooltip' => t('Leave blank to use your theme\'s default page-maintenance.php template'),
                            'for'          => 'path_maintenance'
                        ])
                        ->group('path_maintenance-flex', 'div', function ($form) use ($data) {
                            $form->html('base_path_maintenance', '<span:attr>:content</span>', [
                                ':content' => $this->router->makeRoute(''),
                            ])
                            ->text('path_maintenance', [
                                'class'       => 'form-control',
                                'data-link'   => $this->router->getRoute('api.route'),
                                'placeholder' => t('Example: node/1'),
                                'value'       => $data[ 'path_maintenance' ]
                            ]);
                        }, [ 'class' => 'form-group-flex api_route' ]);
                    }, self::$attrGrp);
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
                    }, self::$attrGrp)
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
                    }, self::$attrGrp)
                    ->group('meta_keyboard-group', 'div', function ($form) use ($data) {
                        $form->label('meta_keyboard-label', t('Keywords'))
                        ->text('meta_keyboard', [
                            'class'       => 'form-control',
                            'placeholder' => t('Word1, Word2, Word3 ...'),
                            'value'       => $data[ 'meta_keyboard' ]
                        ]);
                    }, self::$attrGrp);
                });
    }

    public function validator(&$validator)
    {
        $langs  = implode(',', array_keys($this->translate->getLang())) . ',en';
        $validator->setRules([
            'lang'               => 'required|inarray:' . $langs,
            'timezone'           => 'required|timezone',
            'maintenance'        => '!required|bool',
            'rewrite_engine'     => 'bool',
            'path_index'         => 'route',
            'path_access_denied' => '!required|route',
            'path_no_found'      => '!required|route',
            'path_maintenance'   => '!required|route',
            'meta_title'         => 'required|string|max:64',
            'meta_description'   => 'required|string|max:256',
            'meta_keyboard'      => '!required|string|to_htmlsc',
        ])->setLabels([
            'lang'               => t('Language'),
            'timezone'           => t('Timezone'),
            'maintenance'        => t('Put the site in maintenance'),
            'rewrite_engine'     => t('Make the URLs clean'),
            'path_index'         => t('Default page'),
            'path_access_denied' => t('Page 403 by default (access denied)'),
            'path_no_found'      => t('Page 404 by default (page not found)'),
            'path_maintenance'   => t('Page de maintenance par défaut'),
            'meta_title'         => t('Website title'),
            'meta_description'   => t('Description'),
            'meta_keyboard'      => t('Keywords')
        ]);
    }

    public function before(&$validator, array &$data, $id)
    {
        $data = [
            'lang'               => $validator->getInput('lang'),
            'timezone'           => $validator->getInput('timezone'),
            'maintenance'        => (bool) $validator->getInput('maintenance'),
            'rewrite_engine'     => (bool) $validator->getInput('rewrite_engine'),
            'path_index'         => $validator->getInput('path_index'),
            'path_access_denied' => $validator->getInput('path_access_denied'),
            'path_no_found'      => $validator->getInput('path_no_found'),
            'path_maintenance'   => $validator->getInput('path_maintenance'),
            'meta_title'         => $validator->getInput('meta_title'),
            'meta_description'   => $validator->getInput('meta_description'),
            'meta_keyboard'      => $validator->getInput('meta_keyboard'),
        ];
    }

    public function files(array &$inputsFile)
    {
    }

    public function after(&$validator, array $data, $id)
    {
    }
}
