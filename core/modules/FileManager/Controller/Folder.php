<?php

declare(strict_types=1);

namespace SoosyzeCore\FileManager\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Response;
use Soosyze\Components\Http\Stream;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\FileManager\Form\FormFolder;
use SoosyzeCore\FileManager\Hook\User;
use SoosyzeCore\FileManager\Services\FileManager;
use SoosyzeCore\Template\Services\Block;
use ZipArchive;

class Folder extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    /**
     * @return Block|ResponseInterface
     */
    public function create(string $path, ServerRequestInterface $req)
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

        $action = self::router()->getRoute('filemanager.folder.store', [ ':path' => $path ]);

        $form = (new FormFolder([ 'action' => $action, 'method' => 'post' ]))
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

    public function store(string $path, ServerRequestInterface $req): ResponseInterface
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

    /**
     * @return Block|ResponseInterface
     */
    public function edit(string $path, ServerRequestInterface $req)
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

        $action = self::router()->getRoute('filemanager.folder.update', [ ':path' => $path ]);

        $form = (new FormFolder([ 'action' => $action, 'method' => 'post' ]))
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

    public function update(string $path, ServerRequestInterface $req): ResponseInterface
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

    /**
     * @return Block|ResponseInterface
     */
    public function remove(string $path, ServerRequestInterface $req)
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

        $action = self::router()->getRoute('filemanager.folder.delete', [
            ':path' => $path
        ]);

        $form = (new FormBuilder([ 'action' => $action, 'method' => 'post' ]))
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
                    'info'  => self::filemanager()->parseDir($spl, $path),
                    'menu'  => self::filemanager()->getFolderSubmenu('filemanager.folder.remove', $path),
                    'title' => t('Delete directory')
        ]);
    }

    public function delete(string $path, ServerRequestInterface $req): ResponseInterface
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

    public function download(string $path, ServerRequestInterface $req): ResponseInterface
    {
        $dir = self::core()->getDir('files_public', 'app/files') . $path;
        if (!is_dir($dir)) {
            return $this->get404($req);
        }

        $nameZipArchive     = basename($path) . '-' . Util::strRandom(12) . '.zip';
        $pathnameZipArchive = self::core()->getSetting('tmp_dir') . DS . $nameZipArchive;

        $zipArchive = new ZipArchive();
        $zipArchive->open($pathnameZipArchive, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zipArchive = $this->zipRecursivly($dir, $path, $zipArchive);
        $close      = $zipArchive->close();

        if (!is_file($pathnameZipArchive)) {
            return $this->get404($req);
        }

        $stream = new Stream(fopen($pathnameZipArchive, 'r+'));

        return (new Response(200, $stream))
                ->withHeader('content-type', 'application/octet-stream')
                ->withHeader('content-length', (string) $stream->getSize())
                ->withHeader('content-disposition', 'attachment; filename=' . $nameZipArchive)
                ->withHeader('pragma', 'no-cache')
                ->withHeader('cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->withHeader('expires', '0');
    }

    private function zipRecursivly(string $dir, string $path, ZipArchive $zipArchive): ZipArchive
    {
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isLink()) {
                continue;
            }

            $pathRight = str_replace(
                [ self::core()->getDir('files_public', 'app/files'), '\\' ],
                [ '', '/' ],
                $file->getPathname()
            );

            if ($file->isFile() && $this->isRight($file, $pathRight)) {
                $pathName = str_replace(
                    self::core()->getDir('files_public', 'app/files') . $path,
                    '',
                    $file->getPathname()
                );
                $zipArchive->addFile($file->getPathname(), ltrim(Util::cleanPath($pathName), '/'));
            } elseif ($file->isDir() && $this->isRight($file, $pathRight)) {
                $zipArchive = $this->zipRecursivly($file->getPathname(), $path, $zipArchive);
            }
        }

        return $zipArchive;
    }

    private function isRight(\DirectoryIterator $file, string $path): bool
    {
        /** @var User $hookUser */
        $hookUser = $this->get(User::class);
        $name     = '/' . $file->getBasename('.' . $file->getExtension());
        $ext      = $file->getExtension();

        $accept = true;
        if ($file->isFile()) {
            if (!in_array($ext, FileManager::getExtAllowed())) {
                $accept = false;
            } elseif ($file->getBasename() === '.' . $file->getExtension()) {
                $accept = false;
            } elseif (!$hookUser->hookFileShow($path, $name, $ext)) {
                $accept = false;
            }
        } elseif ($file->isDir() && !$hookUser->hookFolderShow($path . '/' . $file->getBasename())) {
            $accept = false;
        }

        return $accept;
    }
}
