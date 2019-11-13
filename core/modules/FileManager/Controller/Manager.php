<?php

namespace SoosyzeCore\FileManager\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;

class Manager extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin($path, $req)
    {
        $user    = self::user()->isConnected();
        $profils = self::fileprofil()->getProfilsFileByUser($user[ 'user_id' ]);
        if (empty($profils)) {
            return $this->get404();
        } else {
            $path = $profils[ 0 ][ 'folder_show' ];
            $path = str_replace('%uid', $user[ 'user_id' ], $path);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-folder"></i> ' . t('File manager')
                ])
                ->make('page.content', 'page-manager.php', $this->pathViews, [
                    'filemanager' => $this->show($path, $req)
                ])->override('page', [ 'page-fuild.php' ]);
    }

    public function show($path, $req)
    {
        $path = Util::cleanPath($path);
        $form = (new FormBuilder([
            'action'  => self::router()->getRoute('filemanager.file.store', [ ':path' => $path ]),
            'class'   => 'dropfile',
            'method'  => 'post',
            'onclick' => 'document.getElementById(\'file\').click();',
            ]))
            ->group('filemanager-group', 'div', function ($form) {
                $form->label('filemanager-box_file-label', '<i class="fa fa-download"></i> <span class="choose">' . t('Choose a file') . '</span> ' . t('or drag it here.'))
            ->file('file', [
                'multiple' => 1,
                'style'    => 'display:none' ]);
            });

        $breadcrumb = self::template()
            ->createBlock('breadcrumb.php', $this->pathViews)
            ->addVar('links', self::filemanager()->getBreadcrumb($path));

        $files_public = self::core()->getDir('files_public', 'app/files') . $path;
        $files        = [];
        $nb_dir       = 0;
        $size         = 0;
        $nb_file      = 0;

        if (is_dir($files_public)) {
            $dir_iterator = new \DirectoryIterator($files_public);
            $iterator     = $this->get('filemanager.filter.iterator')->load($path, $dir_iterator);
            foreach ($iterator as $file) {
                try {
                    if ($file->isDir()) {
                        $nb_dir++;
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
                        $nb_file++;
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
                    'granted_folder_create' => $this->get('filemanager.hook.user')->hookFolderStore($path),
                    'granted_file_create'   => $this->get('filemanager.hook.user')->hookFileStore($path),
                    'link_show'             => self::router()->getRoute('filemanager.show', [
                        ':path' => $path
                    ]),
                    'link_add'              => self::router()->getRoute('filemanager.folder.create', [
                        ':path' => $path
                    ]),
                    'form'                  => $form,
                    'files'                 => $files,
                    'nb_dir'                => $nb_dir,
                    'nb_file'               => $nb_file,
                    'size_all'              => Util::strFileSizeFormatted($size)
                ])
                ->addBlock('breadcrumb', $breadcrumb);
    }
}
