<?php

namespace SoosyzeCore\FileManager\Controller;

use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class Manager extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin($req)
    {
        $user    = self::user()->isConnected();
        $profils = self::fileprofil()->getProfilsFileByUser($user[ 'user_id' ]);

        if (empty($profils)) {
            return $this->get404();
        }

        $filemanager = null;
        /*
         * Le profil par défaut est derterminé par le premier profil trouvé.
         * Si aucun profil n'est trouvé, aucun aperçu du filemanager ne sera disponible.
         */
        foreach ($profils as $profil) {
            $path = $profil[ 'folder_show' ];
            $path = str_replace(':user_id', $user[ 'user_id' ], $path);
            /* Si le profil est mal écrit. */
            $path = Util::strSlug($path, '-', '\/');

            /* Si le profil trouvé permet d'être vu. */
            if ($this->get('filemanager.hook.user')->hookFolderShow($path)) {
                $filemanager = $this->getFileManager($path, $req);

                break;
            }
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-folder" aria-hidden="true"></i>',
                    'title_main' => t('File manager')
                ])
                ->make('page.content', 'filemanager/content-file_manager-admin.php', $this->pathViews, [
                    'filemanager' => $filemanager
                ])->override('page', [ 'page-fuild.php' ]);
    }

    public function showPublic($path, $req)
    {
        return self::template()
                ->view('page', [
                    'icon'       => '<i class="fa fa-folder" aria-hidden="true"></i>',
                    'title_main' => t('File manager')
                ])
                ->make('page.content', 'page-manager.php', $this->pathViews, [
                    'filemanager' => $this->getFileManager($path, $req)
                ])->override('page', [ 'page-filemanager-public.php', 'page-fuild.php' ]);
    }

    public function show($path, $req)
    {
        return $this->getFileManager($path, $req);
    }

    public function filter($path, $req)
    {
        $path = Util::cleanPath('/' . $path);

        $validator = (new Validator())
            ->setRules([
                'name' => '!required|string|max:255'
            ])
            ->setInputs($req->getQueryParams());

        $params = [ 'name' => '' ];
        if ($validator->getInput('name', '')) {
            $params[ 'name' ] = preg_quote($validator->getInput('name'));
        }

        $filesPublic = self::core()->getDir('files_public', 'app/files') . $path;

        $files  = [];
        $nbDir  = 0;
        $size   = 0;
        $nbFile = 0;

        if (is_dir($filesPublic)) {
            $dirIterator = new \DirectoryIterator($filesPublic);
            $iterator    = $this->get('filemanager.filter.iterator')->load($path, $dirIterator);
            foreach ($iterator as $file) {
                try {
                    $spl = $file->isDir()
                        ? self::filemanager()->parseDir($file, "$path/", $file->getBasename())
                        : self::filemanager()->parseFile($file, $path);

                    if (isset($params[ 'name' ])) {
                        if (!preg_match('/' . $params[ 'name' ] . '/i', $spl[ 'name' ])) {
                            continue;
                        }
                        $spl[ 'name' ] = $this->highlight($params[ 'name' ], $spl[ 'name' ]);
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

            usort($files, function ($a, $b) {
                if ($a['ext'] === $b['ext']) {
                    return 0;
                }

                return ($a['ext'] === 'dir')
                    ? -1
                    : 1;
            });
        }

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/table-files.php', $this->pathViews)
                ->addVars([
                    'files'               => $files,
                    'link_show'           => self::router()->getRoute('filemanager.show', [
                        ':path' => $path
                    ]),
                    'nb_dir'              => $nbDir,
                    'nb_file'             => $nbFile,
                    'profil'              => $this->get('filemanager.hook.user')->getRight($path),
                    'size_all'            => Util::strFileSizeFormatted($size)
                ]);
    }

    protected function highlight($needle, $haystack, $classHighlight = 'highlight')
    {
        return $needle === ''
            ? $haystack
            : preg_replace('/' . preg_quote($needle, '/') . '/i', "<span class='$classHighlight'>$0</span>", $haystack);
    }

    private function getFileManager($path, $req)
    {
        $path = Util::cleanPath('/' . $path);

        $breadcrumb = self::template()
            ->getTheme('theme_admin')
            ->createBlock('filemanager/breadcrumb-file_manager-show.php', $this->pathViews)
            ->addVars([
            'granted_folder_create' => $this->get('filemanager.hook.user')->hookFolderStore($path),
            'links'                 => self::filemanager()->getBreadcrumb($path),
            'link_folder_create'    => self::router()->getRoute('filemanager.folder.create', [
                ':path' => $path
            ]),
        ]);

        $form = (new \Soosyze\Components\Form\FormBuilder([
                'action' => self::router()->getRoute('filemanager.filter', [
                    ':path' => $path
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
                ->createBlock('filemanager/content-file_manager-show.php', $this->pathViews)
                ->addVars([
                    'form'                => $form,
                    'granted_file_create' => $this->get('filemanager.hook.user')->hookFileStore($path),
                    'link_show'           => self::router()->getRoute('filemanager.show', [
                        ':path' => $path
                    ]),
                    'link_file_create'    => self::router()->getRoute('filemanager.file.create', [
                        ':path' => $path
                    ])
                ])
                ->addBlock('breadcrumb', $breadcrumb)
                ->addBlock('table', $this->filter($path, $req));
    }
}
