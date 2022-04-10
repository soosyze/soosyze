<?php

declare(strict_types=1);

namespace SoosyzeCore\System\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\System\ExtendTheme;
use SoosyzeCore\System\Form\FormThemeAdmin;
use SoosyzeCore\System\Form\FormThemePublic;

/**
 * @method \SoosyzeCore\System\Services\Composer     composer()
 * @method \SoosyzeCore\FileSystem\Services\file     file()
 * @method \SoosyzeCore\System\Services\Modules      module()
 * @method \SoosyzeCore\Template\Services\Templating template()
 * @method \SoosyzeCore\User\Services\User           user()
 */
class Theme extends \Soosyze\Controller
{
    private const TYPE_ADMIN = 'admin';

    private const TYPE_PUBLIC = 'public';

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function index(): ResponseInterface
    {
        return $this->admin(self::TYPE_PUBLIC);
    }

    public function admin(string $type): ResponseInterface
    {
        $composers = self::composer()->getThemeComposers();

        $activeThemeName = $type === self::TYPE_ADMIN
            ? self::config()->get('settings.theme_admin')
            : self::config()->get('settings.theme');

        $activeTheme = $composers[ $activeThemeName ];

        unset($composers[ $activeThemeName ]);

        $themes = [];

        $params[ 'token' ] = $this->getToken();

        foreach ($composers as $key => $composer) {
            $theme = $composer[ 'extra' ][ 'soosyze' ];

            $addTheme = ($type === self::TYPE_PUBLIC && empty($theme[ 'options' ][ 'admin' ])) ||
                ($type === self::TYPE_ADMIN && !empty($theme[ 'options' ][ 'admin' ]));

            if ($addTheme) {
                $themes[ $key ] = $composer;

                $uriActivate = self::router()
                    ->generateRequest('system.theme.active', [
                        ':type' => $type,
                        ':name' => $theme[ 'title' ]
                    ])
                    ->getUri();

                $themes[ $key ][ 'link_activate' ] = $uriActivate->withQuery(
                    http_build_query($params)
                );
            }
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-paint-brush" aria-hidden="true"></i>',
                    'title_main' => t('Themes')
                ])
                ->view('page.submenu', $this->getListThemeSubmenu($type))
                ->make('page.content', 'system/content-themes-admin.php', $this->pathViews, [
                    'active_theme' => $activeTheme,
                    'link_edit'    => self::module()->has('Block')
                        ? self::router()->generateUrl('block.section.admin', [ ':theme' => $type ])
                        : null,
                    'link_setting' => self::router()->generateUrl('system.theme.edit', [
                        ':type' => $type
                    ]),
                    'themes'       => $themes
        ]);
    }

    public function active(string $type, string $name, ServerRequestInterface $req): ResponseInterface
    {
        $themes = $this->getThemes($type);

        $validator = (new Validator())
            ->setRules([
                'token' => 'token',
                'name'  => 'inarray:' . implode(',', $themes)
            ])
            ->setInputs($req->getQueryParams() + [ 'name' => $name ]);

        if (!$validator->isValid()) {
            return $this->json(400, [
                    'messages' => [ 'errors' => $validator->getKeyErrors() ]
            ]);
        }

        $outInstall = $this->installTheme($type, $name);

        if (empty($outInstall)) {
            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('system.theme.admin', [
                        ':type' => $type
                    ])
            ]);
        }

        return $this->json(400, [
                'messages' => [ 'errors' => $validator->getKeyErrors() ]
        ]);
    }

    public function edit(string $type): ResponseInterface
    {
        /** @phpstan-var array $values */
        $values = self::config()->get('settings');

        $attr = [
            'action'  => self::router()->generateUrl('system.theme.update', [
                ':type' => $type
            ]),
            'class'   => 'form-api',
            'enctype' => 'multipart/form-data',
            'method'  => 'post'
        ];
        $form = $type === self::TYPE_ADMIN
            ? (new FormThemeAdmin($attr))
            : (new FormThemePublic($attr, self::file()));

        $form->setValues($values)
            ->makeFields();

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-paint-brush" aria-hidden="true"></i>',
                    'title_main' => t('Theme settings')
                ])
                ->view('page.submenu', $this->getListThemeSubmenu($type))
                ->make('page.content', 'system/content-form.php', $this->pathViews, [
                    'form'      => $form,
                    'link_edit' => self::router()->generateUrl('system.theme.admin', [
                        ':type' => $type
                    ])
                ]);
    }

    public function update(string $type, ServerRequestInterface $req): ResponseInterface
    {
        $validator = (new Validator())
            ->setRules(
                self::TYPE_ADMIN === $type
                ? [
                'theme_admin_dark' => 'bool'
                ]
                : [
                'favicon' => '!required|image:png,ico|image_dimensions_height:16,310|image_dimensions_width:16,310|max:100Kb',
                'logo'    => '!required|image|max:200Kb'
                ]
            )
            ->setLabels([
                'favicon' => t('Favicon'),
                'logo'    => t('Logo')
            ])
            ->setInputs((array) $req->getParsedBody() + $req->getUploadedFiles());

        $inputsFile = self::TYPE_ADMIN === $type
            ? []
            : [ 'favicon', 'logo' ];

        if ($validator->isValid()) {
            $data = self::TYPE_ADMIN === $type
                ? [
                'theme_admin_dark' => (bool) $validator->getInput('theme_admin_dark')
                ]
                : [];

            foreach ($data as $key => $value) {
                self::config()->set('settings.' . $key, $value);
            }

            foreach ($inputsFile as $file) {
                $this->saveFile($file, $validator);
            }

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                'redirect' => self::router()->generateUrl('system.theme.admin', [ ':type' => $type ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function getListThemeSubmenu(string $keyRoute): array
    {
        $menus = [
            [
                'key'        => self::TYPE_PUBLIC,
                'link'       => self::router()->generateUrl('system.theme.admin', [
                    ':type' => self::TYPE_PUBLIC
                ]),
                'title_link' => t('Public theme')
            ], [
                'key'        => self::TYPE_ADMIN,
                'link'       => self::router()->generateUrl('system.theme.admin', [
                    ':type' => self::TYPE_ADMIN
                ]),
                'title_link' => t('Admin theme')
            ]
        ];

        return [ 'key_route' => $keyRoute, 'menu' => $menus ];
    }

    private function installTheme(string $type, string $title): array
    {
        $errors = [];

        $composers = self::composer()->getThemeComposers();

        if (!isset($composers[ $title ])) {
            $errors[] = t('The :title module does not exist.', [ ':title' => $title ]);
        } elseif ($out = self::composer()->validComposer($title, $composers)) {
            $errors += $out;
        } elseif ($out = self::composer()->validComposerExtendTheme($title, $composers)) {
            $errors += $out;
        } elseif ($out = self::composer()->validComposerExtraTheme($title, $composers)) {
            $errors += $out;
        } elseif ($out = self::composer()->validRequirePhp($title, $composers)) {
            $errors += $out;
        } elseif ($out = self::composer()->validRequireExtLib($title, $composers)) {
            $errors += $out;
        }

        if (!empty($errors)) {
            return $errors;
        }

        /* Installation */
        /** @phpstan-var class-string<ExtendTheme> $extendClass */
        $extendClass = self::composer()->getExtendClass($title, $composers);

        $extend = new $extendClass();

        $extend->boot();

        $composers[ $title ] += [
            'dir'          => $extend->getDir(),
            'translations' => $extend->getTranslations()
        ];

        self::module()->loadTranslations([ $composers[ $title ] ]);

        self::config()->set(
            $type === self::TYPE_ADMIN
                ? 'settings.theme_admin'
                : 'settings.theme',
            $title
        );

        return [];
    }

    private function getThemes(string $type): array
    {
        $out = [];

        $composers = self::composer()->getThemeComposers();

        foreach ($composers as $key => $composer) {
            $theme = $composer[ 'extra' ][ 'soosyze' ];

            $addTheme = ($type === self::TYPE_PUBLIC && empty($theme[ 'options' ][ 'admin' ])) ||
                ($type === self::TYPE_ADMIN && !empty($theme[ 'options' ][ 'admin' ]));

            if ($addTheme) {
                $out[] = $key;
            }
        }

        return $out;
    }

    private function getToken(): string
    {
        $token = uniqid((string) rand(), true);

        $_SESSION[ 'token' ][ 'token' ] = $token;

        $_SESSION[ 'token_time' ][ 'token' ] = time();

        return $token;
    }

    private function saveFile(string $key, Validator $validator): void
    {
        /** @phpstan-var UploadedFileInterface $uploadedFile */
        $uploadedFile = $validator->getInput($key);

        self::file()
            ->add($uploadedFile, $validator->getInputString("file-$key-name"))
            ->setName($key)
            ->setPath('/config')
            ->isResolvePath()
            ->callGet(function (string $key, string $name): ?string {
                $filename = self::config()->get("settings.$key");

                return is_string($filename)
                    ? $filename
                    : null;
            })
            ->callMove(function (string $key, string $name, string $move): void {
                self::config()->set("settings.$key", $move);
            })
            ->callDelete(function (string $key, string $name): void {
                self::config()->set("settings.$key", '');
            })
            ->save();
    }
}
