<?php

namespace SoosyzeCore\FileManager\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class Folder extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $dir = self::core()->getDir('files_public', 'app/files') . $path;
        if (!is_dir($dir)) {
            return $this->get404($req);
        }
        $spl  = new \SplFileInfo($dir);

        $content[ 'name' ] = '';
        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormBuilder([
            'action' => self::router()->getRoute('filemanager.folder.store', [ ':path' => $path ]),
            'method' => 'post',
            ]))
            ->group('folder-fieldset', 'fieldset', function ($form) use ($content) {
                $form->legend('folder-legend', t('Créer un répertoire'))
                ->group('name-group', 'div', function ($form) use ($content) {
                    $form->label('name-label', t('Name'), [
                        'data-tooltip' => t('All non-alphanumeric characters or hyphens will be replaced by an underscore (_) or their unaccented equivalent.')
                    ])
                    ->text('name', [
                        'class'     => 'form-control',
                        'maxlenght' => 255,
                        'required'  => 1,
                        'value'     => $content[ 'name' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_folder_store')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return self::template()
                ->createBlock('modal.php', $this->pathViews)
                ->addVars([
                    'title' => t('Create a new directory'),
                    'info'  => ['actions' => []],
                    'form'  => $form
        ]);
    }

    public function store($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $dir       = self::core()->getDir('files_public', 'app/files') . $path;
        $validator = (new Validator())
            ->setRules([
                'name'               => 'required|string|max:255',
                'dir'                => 'required|dir',
                'token_folder_store' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('dir', $dir);

        $output = [];
        if (!$validator->isValid()) {
            $output[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
            $statut                           = 400;

            return $this->json($statut, $output);
        }

        $folder     = Util::strSlug($validator->getInput('name'));
        $dir_create = "$dir/$folder";

        if (!is_dir($dir_create)) {
            mkdir($dir_create, 0755, true);

            $output[ 'messages' ][ 'success' ] = [ t('The directory is created') ];
            $statut                            = 200;
        } else {
            $output[ 'messages' ][ 'errors' ] = [ t('You can not use this directory name') ];
            $statut                           = 400;
        }

        return $this->json($statut, $output);
    }

    public function edit($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $path = Util::cleanPath($path);
        $dir = self::core()->getDir('files_public', 'app/files') . $path;
        if (!is_dir($dir)) {
            return $this->get404($req);
        }

        $spl  = new \SplFileInfo($dir);
        $data = self::filemanager()->parseDir($spl, $path);
        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormBuilder([
            'action' => self::router()->getRoute('filemanager.folder.update', [ ':path' => $path ]),
            'method' => 'post',
            ]))
            ->group('folder-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('folder-legend', t('Rename the directory'))
                ->group('name-group', 'div', function ($form) use ($data) {
                    $form->label('name-label', t('Name'), [
                        'data-tooltip' => t('All non-alphanumeric characters or hyphens will be replaced by an underscore (_) or their unaccented equivalent.')
                    ])
                    ->text('name', [
                        'class'     => 'form-control',
                        'maxlenght' => 255,
                        'required'  => 1,
                        'value'     => $data[ 'name' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
            })
            ->token('token_folder_update')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return self::template()
                ->createBlock('modal.php', $this->pathViews)
                ->addVars([
                    'title' => t('Rename the directory'),
                    'info'  => $data,
                    'form'  => $form
        ]);
    }

    public function update($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $dir       = self::core()->getDir('files_public', 'app/files') . $path;
        $validator = (new Validator())
            ->setRules([
                'name'                => 'required|string|max:255',
                'dir'                 => 'required|dir',
                'token_folder_update' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('dir', $dir);

        $output = [];
        if (!$validator->isValid()) {
            $output[ 'errors_keys' ]          = $validator->getKeyInputErrors();
            $output[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
            $statut                           = 400;

            return $this->json($statut, $output);
        }

        $folder     = Util::strSlug($validator->getInput('name'));
        $dir_update = dirname($dir) . "/$folder";

        /* Si le nouveau nom du répertoire est déjà utilisé. */
        if (!is_dir($dir_update)) {
            $folder = Util::strSlug($validator->getInput('name'));
            rename($dir, $dir_update);

            $output[ 'messages' ][ 'success' ] = [ t('The directory is renamed') ];
            $statut                            = 200;
        } else {
            $output[ 'errors_keys' ]          = $validator->getKeyInputErrors();
            $output[ 'messages' ][ 'errors' ] = [ t('You can not use this name to rename the directory') ];
            $statut                           = 400;
        }

        return $this->json($statut, $output);
    }

    public function remove($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $dir = self::core()->getDir('files_public', 'app/files') . $path;
        if (!is_dir($dir)) {
            return $this->get404($req);
        }
        $spl  = new \SplFileInfo($dir);
        $data = self::filemanager()->parseDir($spl, $path);

        $form = (new FormBuilder([
            'action' => self::router()->getRoute('filemanager.folder.delete', [ ':path' => $path ]),
            'method' => 'post',
            ]))
            ->group('folder-fieldset', 'fieldset', function ($form) {
                $form->legend('folder-legend', t('Delete directory'))
                ->html('folder-info', '<p:attr>:_content</p>', [
                    '_content' => t('Warning ! The deletion of the directory and its contents is final.')
                ]);
            })
            ->token('token_folder_delete')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return self::template()
                ->createBlock('modal.php', $this->pathViews)
                ->addVars([ 'title' => t('Delete directory'),
                    'info'  => $data,
                    'form'  => $form
        ]);
    }

    public function delete($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }
        $dir       = self::core()->getDir('files_public', 'app/files') . $path;
        $validator = (new Validator())
            ->setRules([
                'dir'                 => 'required|dir',
                'token_folder_delete' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('dir', $dir);

        $output = [];
        if ($validator->isValid()) {
            $dir_iterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
            $iterator     = new \RecursiveIteratorIterator($dir_iterator);

            /* Supprime tous les dossiers et fichiers */
            foreach ($iterator as $file) {
                $file->isDir()
                        ? rmdir($file)
                        : unlink($file);
            }
            /* Supprime le dossier cible. */
            rmdir($dir);

            $output[ 'messages' ][ 'success' ] = [ t('The directory has been deleted') ];
            $statut                            = 200;
        } else {
            $output[ 'errors_keys' ]          = $validator->getKeyInputErrors();
            $output[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
            $statut                           = 400;
        }

        return $this->json($statut, $output);
    }
}
