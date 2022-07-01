<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\FileManager\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\FileManager\Hook\User;
use Soosyze\Core\Modules\FileManager\Services\FilterManagerIterator;
use Soosyze\Core\Modules\Template\Services\Block;

/**
 * @method \Soosyze\Core\Modules\FileManager\Services\FileProfil  fileprofil()
 * @method \Soosyze\Core\Modules\FileManager\Services\FileManager filemanager()
 * @method \Soosyze\Core\Modules\Template\Services\Templating     template()
 * @method \Soosyze\Core\Modules\User\Services\User               user()
 */
class Manager extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin(ServerRequestInterface $req): ResponseInterface
    {
        $user = self::user()->isConnected();
        $profils = self::fileprofil()->getProfilsFileByUser($user[ 'user_id' ] ?? null);
        if (empty($profils)) {
            return $this->get404();
        }

        $filemanager = null;
        /** @var User $hookUser */
        $hookUser = $this->get(User::class);
        $path = '';
        /*
         * Le profil par défaut est derterminé par le premier profil trouvé.
         * Si aucun profil n'est trouvé, aucun aperçu du filemanager ne sera disponible.
         */
        foreach ($profils as $profil) {
            $path = $profil[ 'folder_show' ];
            $path = str_replace(':user_id', $user[ 'user_id' ] ?? '0', $path);
            /* Si le profil est mal écrit. */
            $path = Util::strSlug($path, '-', '\/');

            /* Si le profil trouvé permet d'être vu. */
            if ($hookUser->hookFolderShow($path)) {
                $filemanager = $this->getFileManager($path, $req);

                break;
            }
        }

        $form = (new FormBuilder([
                'action' => self::router()->generateUrl('filemanager.filter', [
                    'path' => Util::cleanPath('/' . $path)
                ]),
                'id'     => 'form_filter_file',
                'method' => 'get'
            ]))
            ->group('name-group', 'div', function ($form) {
                $form->text('name', [
                    'autofocus'   => 1,
                    'class'       => 'form-control',
                    'placeholder' => t('Search for items in the directory')
                ]);
            }, [ 'class' => 'form-group' ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-folder" aria-hidden="true"></i>',
                    'title_main' => t('File manager')
                ])
                ->make('page.content', 'filemanager/content-file_manager-admin.php', $this->pathViews, [
                    'filemanager' => $filemanager,
                    'form'        => $form
                ])->override('page', [ 'page-fuild.php' ]);
    }

    public function showPublic(string $path, ServerRequestInterface $req): ResponseInterface
    {
        return self::template()
                ->view('page', [
                    'icon'       => '<i class="fa fa-folder" aria-hidden="true"></i>',
                    'title_main' => t('File manager')
                ])
                ->make('page.content', 'filemanager/content-file_manager-admin.php', $this->pathViews, [
                    'filemanager' => $this->getFileManager($path, $req),
                    'form'        => null
                ])->override('page', [ 'content-file_manager-public.php' ]);
    }

    public function show(string $path, ServerRequestInterface $req): Block
    {
        return $this->getFileManager($path, $req);
    }

    public function filter(string $path, ServerRequestInterface $req): Block
    {
        $path = Util::cleanPath('/' . $path);

        $validator = (new Validator())
            ->setRules([
                'name' => '!required|string|max:255'
            ])
            ->setInputs($req->getQueryParams());

        $params = [ 'name' => '' ];

        if ($validator->isValid()) {
            $params[ 'name' ] = preg_quote($validator->getInputString('name'), '/');
        }

        $filesPublic = self::core()->getDir('files_public', 'app/files') . $path;

        $files  = [];
        $nbDir  = 0;
        $size   = 0;
        $nbFile = 0;

        if (is_dir($filesPublic)) {
            $dirIterator = new \DirectoryIterator($filesPublic);

            /** @var FilterManagerIterator $iterator */
            $iterator = $this->get(FilterManagerIterator::class);
            $iterator = $iterator->load($path, $dirIterator);

            /** @phpstan-var \DirectoryIterator $file */
            foreach ($iterator as $file) {
                try {
                    $spl = $file->isDir()
                        ? self::filemanager()->parseDir($file, "$path/")
                        : self::filemanager()->parseFile($file, $path);

                    if (!empty($params[ 'name' ])) {
                        if (!preg_match('/' . $params[ 'name' ] . '/i', $spl[ 'name' ])) {
                            continue;
                        }
                        $spl[ 'name' ] = Util::strHighlight($params[ 'name' ], $spl[ 'name' ]);
                    }
                    $file->isDir()
                            ? ++$nbDir
                            : ++$nbFile;

                    $files[] = $spl;
                    $size    += $spl[ 'size_octet' ];
                } catch (\Exception $e) {
                    continue;
                }
            }

            usort($files, static function (array $a, array $b): int {
                if ($a[ 'ext' ] === $b[ 'ext' ]) {
                    return 0;
                }

                return ($a[ 'ext' ] === 'dir')
                    ? -1
                    : 1;
            });
        }

        /** @var User $hookUser */
        $hookUser = $this->get(User::class);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/table-files.php', $this->pathViews)
                ->addVars([
                    'files'       => $files,
                    'link_search' => self::router()->generateUrl('filemanager.filter', [
                        'path' => $path
                    ]),
                    'link_show'   => self::router()->generateUrl('filemanager.show', [
                        'path' => $path
                    ]),
                    'nb_dir'      => $nbDir,
                    'nb_file'     => $nbFile,
                    'profil'      => $hookUser->getRight($path),
                    'size_all'    => Util::strFileSizeFormatted($size)
        ]);
    }

    private function getFileManager(string $path, ServerRequestInterface $req): Block
    {
        $path = Util::cleanPath('/' . $path);

        /** @var User $hookUser */
        $hookUser = $this->get(User::class);

        $breadcrumb = self::template()
            ->getTheme('theme_admin')
            ->createBlock('filemanager/breadcrumb-file_manager-show.php', $this->pathViews)
            ->addVars([
            'granted_folder_create' => $hookUser->hookFolderStore($path),
            'links'                 => self::filemanager()->getBreadcrumb($path),
            'link_folder_create'    => self::router()->generateUrl('filemanager.folder.create', [
                'path' => $path
            ]),
        ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/content-file_manager-show.php', $this->pathViews)
                ->addVars([
                    'granted_file_create' => $hookUser->hookFileStore($path),
                    'link_show'           => self::router()->generateUrl('filemanager.show', [
                        'path' => $path
                    ]),
                    'link_file_create'    => self::router()->generateUrl('filemanager.file.create', [
                        'path' => $path
                    ])
                ])
                ->addBlock('breadcrumb', $breadcrumb)
                ->addBlock('table', $this->filter($path, $req));
    }
}
