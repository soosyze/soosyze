<?php

namespace SoosyzeCore\FileManager\Controller;

use Soosyze\Components\Util\Util;

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
                $filemanager = $this->getFileManager($path);

                break;
            }
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-folder" aria-hidden="true"></i>',
                    'title_main' => t('File manager')
                ])
                ->make('page.content', 'page-manager.php', $this->pathViews, [
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
                    'filemanager' => $this->getFileManager($path)
                ])->override('page', [ 'page-filemanager-public.php', 'page-fuild.php' ]);
    }

    public function show($path, $req)
    {
        return $this->getFileManager($path);
    }

    private function getFileManager($path)
    {
        $path = Util::cleanPath('/' . $path);

        $breadcrumb = self::template()
            ->createBlock('breadcrumb.php', $this->pathViews)
            ->addVars([
            'granted_folder_create' => $this->get('filemanager.hook.user')->hookFolderStore($path),
            'links'                 => self::filemanager()->getBreadcrumb($path),
            'link_folder_create'    => self::router()->getRoute('filemanager.folder.create', [
                ':path' => $path
            ]),
        ]);
        
        $filesPublic = self::core()->getDir('files_public', 'app/files') . $path;
        $files       = [];
        $nbDir       = 0;
        $size        = 0;
        $nbFile      = 0;

        if (is_dir($filesPublic)) {
            $dirIterator = new \DirectoryIterator($filesPublic);
            $iterator    = $this->get('filemanager.filter.iterator')->load($path, $dirIterator);
            foreach ($iterator as $file) {
                try {
                    if ($file->isDir()) {
                        $nbDir++;
                        $spl     = self::filemanager()->parseDir($file, "$path/", $file->getBasename());
                        $size    += $spl[ 'size_octet' ];
                        $files[] = $spl;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            foreach ($iterator as $file) {
                try {
                    if ($file->isFile()) {
                        $nbFile++;
                        $spl     = self::filemanager()->parseFile($file, $path);
                        $size    += $spl[ 'size_octet' ];
                        $files[] = $spl;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return self::template()
                ->createBlock('filemanager-show.php', $this->pathViews)
                ->addVars([
                    'files'               => $files,
                    'granted_file_create' => $this->get('filemanager.hook.user')->hookFileStore($path),
                    'link_show'           => self::router()->getRoute('filemanager.show', [
                        ':path' => $path
                    ]),
                    'link_file_create'    => self::router()->getRoute('filemanager.file.create', [
                        ':path' => $path
                    ]),
                    'nb_dir'              => $nbDir,
                    'nb_file'             => $nbFile,
                    'profil'              => $this->get('filemanager.hook.user')->getRight($path),
                    'size_all'            => Util::strFileSizeFormatted($size)
                ])
                ->addBlock('breadcrumb', $breadcrumb);
    }
}
