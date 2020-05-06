<?php

namespace SoosyzeCore\FileManager\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Response;
use Soosyze\Components\Http\Stream;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\FileManager\Services\FileManager;

class File extends \Soosyze\Controller
{
    protected static $extensionImage = [
        'gif', 'ico', 'jpg', 'jpeg', 'png'
    ];

    protected static $extensionCode = [
        'css', 'csv', 'html', 'json', 'txt', 'xhtml', 'xml'
    ];

    protected static $extensionVideo = [
        'mp4', 'mpeg'
    ];

    protected static $extensionAudio = [
        'mp3'
    ];

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function show($path, $name, $ext, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $spl = new \SplFileInfo(
            self::core()->getDir('files_public', 'app/files') . "$path$name$ext"
        );
        if (!$spl->isFile()) {
            return $this->get404($req);
        }
        $data = self::filemanager()->parseFile($spl, $path);

        return self::template()
                ->createBlock('modal-show.php', $this->pathViews)
                ->addBlock('visualize', $this->visualizeFile($data, self::core()->getPath('files_public', 'app/files') . "$path$name$ext"))
                ->addVars([
                    'file'  => $spl,
                    'info'  => $data,
                    'title' => t('See the file') ]);
    }

    public function store($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }
        if ($req->isMaxSize()) {
            $output[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];

            return $this->json(400, $output);
        }

        $dir    = self::core()->getDir('files_public', 'app/files') . $path;
        $profil = $this->get('filemanager.hook.user')->getRight($path);
        $rules  = [
            'file'   => 'required',
            'folder' => '!required',
        ];

        if (!empty($profil[ 'file_extensions_all' ])) {
            $rules[ 'file' ] .= '|file_extensions:' . implode(',', FileManager::getWhiteList());
        } else {
            $rules[ 'file' ] .= '|file_extensions:' . $profil[ 'file_extensions' ];
        }

        if (!empty($profil[ 'file_size' ])) {
            $rules[ 'file' ] .= '|max:' . $profil[ 'file_size' ] . 'mb';
        }
        if (!empty($profil[ 'folder_size' ])) {
            $rules[ 'folder' ] = 'max:' . $profil[ 'folder_size' ] . 'mb';
        }

        $validator = (new Validator())
            ->setRules($rules)
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());

        if (is_dir($dir)) {
            $validator->addInput('folder', self::filemanager()->parseRecursive($dir)[ 'size' ]);
        } else {
            $validator->addInput('folder', 0);
        }

        if (!$validator->isValid()) {
            $output[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

            return $this->json(400, $output);
        }

        $file        = $validator->getInput('file');
        $serviceFile = self::file();

        if (self::config()->get('settings.replace_file') === 2) {
            $serviceFile = self::file()->setResolveName();
        } elseif (self::config()->get('settings.replace_file') === 3 && is_file($dir . '/' . $file->getClientFilename())) {
            $output[ 'messages' ][ 'errors' ][] = t('An existing file has the same name, you can not replace it');

            return $this->json(400, $output);
        }

        $serviceFile
            ->add($file)
            ->setPath($dir)
            ->setResolvePath()
            ->saveOne();

        $output[ 'messages' ][ 'success' ][] = t('The file has been uploaded');

        return $this->json(200, $output);
    }

    public function edit($path, $name, $ext, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $spl = new \SplFileInfo(
            self::core()->getDir('files_public', 'app/files') . "$path$name$ext"
        );
        if (!$spl->isFile()) {
            return $this->get404($req);
        }
        $data = self::filemanager()->parseFile($spl, $path);

        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = (new FormBuilder([
            'action' => self::router()->getRoute('filemanager.file.update', [
                ':path' => $path,
                ':name' => $name,
                ':ext'  => $ext
            ]),
            'method' => 'post',
            ]))
            ->group('file-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('file-legend', t('Rename the file'))
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
            ->token('token_file_update')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return self::template()
                ->createBlock('modal.php', $this->pathViews)
                ->addVars([
                    'title' => t('Rename the file'),
                    'info'  => $data,
                    'form'  => $form ]);
    }

    public function update($path, $name, $ext, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $fileOld = self::core()->getDir('files_public', 'app/files') . "$path$name$ext";
        if (!is_file($fileOld)) {
            return $this->get404($req);
        }
        $dir = self::core()->getDir('files_public', 'app/files') . $path;

        $validator = (new Validator())
            ->setRules([
                'name'              => 'required|string|max:255',
                'dir'               => 'required|dir',
                'token_file_update' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('dir', $dir);

        $output = [];
        /* Si les valeur attendues sont les bonnes. */
        if (!$validator->isValid()) {
            $output[ 'errors_keys' ]          = $validator->getKeyInputErrors();
            $output[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

            return $this->json(400, $output);
        }

        $folder     = Util::strSlug($validator->getInput('name'));
        $fileUpdate = "$dir/$folder$ext";

        /* Si le nouveau nom du fichier est déjà utilisé. */
        if (!is_file($fileUpdate)) {
            $folder = Util::strSlug($validator->getInput('name'));
            rename($fileOld, $fileUpdate);

            $output[ 'messages' ][ 'success' ] = [ t('The file has been renamed') ];

            return $this->json(200, $output);
        }

        $output[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        $output[ 'messages' ][ 'errors' ] = [ t('You can not use this name to rename the file') ];

        return $this->json(400, $output);
    }

    public function remove($path, $name, $ext, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $spl = new \SplFileInfo(
            self::core()->getDir('files_public', 'app/files') . "$path$name$ext"
        );
        if (!$spl->isFile()) {
            return $this->get404($req);
        }

        $form = (new FormBuilder([
            'action' => self::router()->getRoute('filemanager.file.delete', [
                ':path' => $path,
                ':name' => $name,
                ':ext'  => $ext
            ]),
            'method' => 'post',
            ]))
            ->group('folder-fieldset', 'fieldset', function ($form) use ($name, $ext) {
                $form->legend('folder-legend', t('Deleting the file'))
                ->html('folder-info', '<p:attr>:_content</p>', [
                    '_content' => t('Warning ! The deletion of the @name file is final.', [
                        '@name' => "$name$ext"
                    ])
                ]);
            })
            ->token('token_file_delete')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return self::template()
                ->createBlock('modal.php', $this->pathViews)
                ->addVars([ 'title' => t('Deleting the file'),
                    'info'  => self::filemanager()->parseFile($spl, $path),
                    'form'  => $form ]);
    }

    public function delete($path, $name, $ext, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $file = self::core()->getDir('files_public', 'app/files') . "$path$name$ext";
        if (!is_file($file)) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'dir'               => 'required|dir',
                'token_file_delete' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('dir', self::core()->getDir('files_public', 'app/files') . $path);

        $output = [];
        if ($validator->isValid()) {
            unlink($file);
            $output[ 'messages' ][ 'success' ] = [ t('The file has been deleted') ];

            return $this->json(200, $output);
        }

        $output[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        $output[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

        return $this->json(400, $output);
    }

    public function download($path, $name, $ext, $req)
    {
        $file = self::core()->getDir('files_public', 'app/files') . "$path$name$ext";
        if (!is_file($file)) {
            return $this->get404($req);
        }

        $stream = new Stream(fopen($file, 'r+'));

        return (new Response(200, $stream))
                ->withHeader('content-type', 'application/octet-stream')
                ->withHeader('content-length', $stream->getSize())
                ->withHeader('content-disposition', 'attachment; filename=' . substr("$name$ext", 1))
                ->withHeader('pragma', 'no-cache')
                ->withHeader('cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->withHeader('expires', '0');
    }

    protected function visualizeFile(array $info, $path)
    {
        if (in_array($info[ 'ext' ], self::$extensionImage)) {
            return self::template()
                    ->createBlock('file-show-image.php', $this->pathViews . '/show/')
                    ->addVars([ 'path' => $path ]);
        }
        if (in_array($info[ 'ext' ], self::$extensionCode)) {
            $code = file_get_contents($path);

            return self::template()
                    ->createBlock('file-show-code.php', $this->pathViews . '/show/')
                    ->addVars([
                        'code'      => htmlspecialchars($code),
                        'extension' => $info[ 'ext' ]
            ]);
        }
        if (in_array($info[ 'ext' ], self::$extensionVideo)) {
            return self::template()
                    ->createBlock('file-show-video.php', $this->pathViews . '/show/')
                    ->addVars([
                        'path'      => $path,
                        'extension' => $info[ 'ext' ]
            ]);
        }
        if (in_array($info[ 'ext' ], self::$extensionAudio)) {
            return self::template()
                    ->createBlock('file-show-audio.php', $this->pathViews . '/show/')
                    ->addVars([
                        'path'      => $path,
                        'extension' => $info[ 'ext' ]
            ]);
        }
        if ($output = self::core()->callHook('filemanager.visualize', [ $info[ 'ext' ] ])) {
            return $output;
        }

        return self::template()
                ->createBlock('file-show-default.php', $this->pathViews . '/show/')
                ->addVars([ 'extension' => $info[ 'ext' ] ]);
    }
}
