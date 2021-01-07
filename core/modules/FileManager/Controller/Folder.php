<?php

namespace SoosyzeCore\FileManager\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\FileManager\Form\FormFolder;

class Folder extends \Soosyze\Controller
{
    private $pathViews;

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $spl = new \SplFileInfo(
            self::core()->getDir('files_public', 'app/files') . $path
        );

        $values = [];
        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormFolder([
            'action' => self::router()->getRoute('filemanager.folder.store', [ ':path' => $path ]),
            'method' => 'post',
            ]))
            ->setValues($values)
            ->makeFields();

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'title' => t('Create a new directory')
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
                'name'         => 'required|string|max:255',
                'token_folder' => 'token'
            ])
            ->addLabel('name', t('Name'))
            ->setInputs($req->getParsedBody());

        $out = [];
        if (!$validator->isValid()) {
            $out[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

            return $this->json(400, $out);
        }

        $folder = Util::strSlug($validator->getInput('name'));
        $newDir = "$dir/$folder";

        if (!is_dir($newDir)) {
            mkdir($newDir, 0755, true);

            $out[ 'messages' ][ 'success' ] = [ t('The directory is created') ];

            return $this->json(200, $out);
        }

        $out[ 'messages' ][ 'errors' ] = [ t('You can not use this directory name') ];

        return $this->json(400, $out);
    }

    public function edit($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $path = Util::cleanPath($path);
        $spl  = new \SplFileInfo(
            self::core()->getDir('files_public', 'app/files') . $path
        );
        if (!$spl->isDir()) {
            return $this->get404($req);
        }

        $values = self::filemanager()->parseDir($spl, $path);
        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormFolder([
            'action' => self::router()->getRoute('filemanager.folder.update', [ ':path' => $path ]),
            'method' => 'post',
            ]))
            ->setValues($values)
            ->makeFields();

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'info'  => $values,
                    'menu'  => self::filemanager()->getFolderSubmenu('filemanager.folder.edit', $path),
                    'title' => t('Rename the directory')
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
                'name'         => 'required|string|max:255',
                'dir'          => 'required|dir',
                'token_folder' => 'token'
            ])
            ->addLabel('name', t('Name'))
            ->setInputs($req->getParsedBody())
            ->addInput('dir', $dir);

        $out = [];
        if (!$validator->isValid()) {
            $out[ 'errors_keys' ]          = $validator->getKeyInputErrors();
            $out[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

            return $this->json(400, $out);
        }

        $folder    = Util::strSlug($validator->getInput('name'));
        $dirUpdate = dirname($dir) . "/$folder";

        /* Si le nouveau nom du répertoire est déjà utilisé. */
        if ($dir === $dirUpdate || !is_dir($dirUpdate)) {
            rename($dir, $dirUpdate);

            $out[ 'messages' ][ 'success' ] = [ t('The directory is renamed') ];

            return $this->json(200, $out);
        }

        $out[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        $out[ 'messages' ][ 'errors' ] = [ t('You can not use this name to rename the directory') ];

        return $this->json(400, $out);
    }

    public function remove($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $spl = new \SplFileInfo(
            self::core()->getDir('files_public', 'app/files') . $path
        );
        if (!$spl->isDir()) {
            return $this->get404($req);
        }
        $values = self::filemanager()->parseDir($spl, $path);

        $form = (new FormBuilder([
                'action' => self::router()->getRoute('filemanager.folder.delete', [
                    ':path' => $path
                ]),
                'method' => 'post',
            ]))
            ->group('folder-fieldset', 'fieldset', function ($form) {
                $form->legend('folder-legend', t('Delete directory'))
                ->group('info-group', 'div', function ($form) {
                    $form->html('info', '<p:attr>:content</p>', [
                        ':content' => t('Warning ! The deletion of the directory and its contents is final.')
                    ]);
                }, [ 'class' => 'alert alert-warning' ]);
            })
            ->token('token_folder_delete')
            ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'info'  => $values,
                    'menu'  => self::filemanager()->getFolderSubmenu('filemanager.folder.remove', $path),
                    'title' => t('Delete directory')
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

        $out = [];
        if ($validator->isValid()) {
            $dirIterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
            $iterator    = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::CHILD_FIRST);

            /* Supprime tous les dossiers et fichiers */
            foreach ($iterator as $file) {
                $file->isDir()
                        ? rmdir($file)
                        : unlink($file);
            }
            /* Supprime le dossier cible. */
            rmdir($dir);

            $out[ 'messages' ][ 'success' ] = [ t('The directory has been deleted') ];

            return $this->json(200, $out);
        }

        $out[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        $out[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

        return $this->json(400, $out);
    }
}
