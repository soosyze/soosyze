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

    protected $pathViews;

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
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-file-show.php', $this->pathViews)
                ->addBlock('visualize', $this->visualizeFile($data, self::core()->getPath('files_public', 'app/files') . "$path$name$ext"))
                ->addVars([
                    'file'  => $spl,
                    'info'  => $data,
                    'menu'  => self::filemanager()->getFileSubmenu('filemanager.file.show', $spl, $path),
                    'title' => t('See the file')
        ]);
    }

    public function create($path, $req)
    {
        $path = Util::cleanPath($path);
        $max  = $this->get('filemanager.hook.user')->getMaxUpload($path);

        $form = (new FormBuilder([
                'action'  => self::router()->getRoute('filemanager.file.store', [
                    ':path' => $path
                ]),
                'class'   => 'filemanager-dropfile',
                'method'  => 'post',
                'onclick' => 'document.getElementById(\'file\').click();',
                ]))
            ->group('file-group', 'div', function ($form) use ($max) {
                $form->label(
                    'file-label',
                    '<div class="filemanager-dropfile__progress">'
                . '<span class="filemanager-dropfile__progress_percent"></span>'
                . '<div class="filemanager-dropfile__progress_bar">'
                . '</div>'
                . '</div>'
                . '<div class="filemanager-dropfile__label">'
                . '<i class="fa fa-download" aria-hidden="true"></i> <span class="choose">'
                . t('Choose a file')
                . '</span> '
                . t('or drag it here.')
                . '<p><small>'
                . t('File size is limited to :size per upload', [
                    ':size' => Util::strFileSizeFormatted($max)
                ])
                . '</small></p>'
                . '</div>'
                )
            ->file('file', [
                'multiple' => 1,
                'style'    => 'display:none'
            ]);
            });

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'title' => t('Add a new file')
        ]);
    }

    public function store($path, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }
        if ($req->isMaxSize()) {
            $out[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];

            return $this->json(400, $out);
        }

        $dir    = self::core()->getDir('files_public', 'app/files') . $path;
        $profil = $this->get('filemanager.hook.user')->getRight($path);
        $rules  = [
            'file'   => 'required',
            'folder' => '!required',
        ];

        if (!empty($profil[ 'file_extensions_all' ])) {
            $rules[ 'file' ] .= '|file_extensions:' . implode(',', FileManager::getExtAllowed());
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
            ->addLabel('file', t('File'))
            ->setMessages([
                'file' => [
                    'max' => [
                        'must' => t('File size is limited to :size per upload')
                    ]
                ],
                'folder' => [
                    'max' => [
                        'must' => t('You exceed your quota of :max of data authorized in this directory')
                    ]
                ]
            ])
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());

        if (is_dir($dir)) {
            $sizeFile   = $validator->getInput('file', 0)
                ? $validator->getInput('file')->getSize()
                : 0;
            $sizefolder = self::filemanager()->parseRecursive($dir)[ 'size' ];

            $validator->addInput('folder', $sizefolder + $sizeFile);
        } else {
            $validator->addInput('folder', 0);
        }

        if (!$validator->isValid()) {
            $out[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

            return $this->json(400, $out);
        }

        $file        = $validator->getInput('file');
        $serviceFile = self::file();

        if (self::config()->get('settings.replace_file') === 2) {
            $serviceFile = self::file()->setResolveName();
        } elseif (self::config()->get('settings.replace_file') === 3 && is_file($dir . '/' . $file->getClientFilename())) {
            $out[ 'messages' ][ 'errors' ][] = t('An existing file has the same name, you can not replace it');

            return $this->json(400, $out);
        }

        $serviceFile
            ->add($file)
            ->setPath($dir)
            ->setResolvePath()
            ->saveOne();

        $out[ 'messages' ][ 'success' ][] = t('The file has been uploaded');

        return $this->json(200, $out);
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
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'info'  => $data,
                    'menu'  => self::filemanager()->getFileSubmenu('filemanager.file.edit', $spl, $path),
                    'title' => t('Rename the file'),
        ]);
    }

    public function update($path, $name, $ext, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $dir         = self::core()->getDir('files_public', 'app/files') . $path;
        $fileCurrent = "$dir$name$ext";

        $validator = (new Validator())
            ->setRules([
                'dir'               => 'required|dir',
                'file_current'      => 'required|is_file',
                'name'              => 'required|string|max:255',
                'token_file_update' => 'token'
            ])
            ->addLabel('name', t('Name'))
            ->setInputs($req->getParsedBody())
            ->addInput('dir', $dir)
            ->addInput('file_current', $fileCurrent);

        $out = [];
        /* Si les valeur attendues sont les bonnes. */
        if (!$validator->isValid()) {
            $out[ 'errors_keys' ]          = $validator->getKeyInputErrors();
            $out[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

            return $this->json(400, $out);
        }

        $nameUpdate = Util::strSlug($validator->getInput('name'));
        $fileUpdate = "$dir/$nameUpdate$ext";

        /* Si le nouveau nom du fichier est déjà utilisé. */
        if ($fileCurrent === $fileUpdate || !is_file($fileUpdate)) {
            rename($fileCurrent, $fileUpdate);

            $out[ 'messages' ][ 'success' ] = [ t('The file has been renamed') ];

            return $this->json(200, $out);
        }

        $out[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        $out[ 'messages' ][ 'errors' ] = [ t('You can not use this name to rename the file') ];

        return $this->json(400, $out);
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
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'  => $form,
                    'info'  => self::filemanager()->parseFile($spl, $path),
                    'menu'  => self::filemanager()->getFileSubmenu('filemanager.file.remove', $spl, $path),
                    'title' => t('Deleting the file')
        ]);
    }

    public function delete($path, $name, $ext, $req)
    {
        if (!$req->isAjax()) {
            return $this->get404($req);
        }

        $dir  = self::core()->getDir('files_public', 'app/files') . $path;
        $file = "$dir$name$ext";

        $validator = (new Validator())
            ->setRules([
                'dir'               => 'required|dir',
                'file'              => 'required|is_file',
                'token_file_delete' => 'token'
            ])
            ->setInputs($req->getParsedBody())
            ->addInput('dir', $dir)
            ->addInput('file', $file);

        $out = [];
        if ($validator->isValid()) {
            unlink($file);
            $out[ 'messages' ][ 'success' ] = [ t('The file has been deleted') ];

            return $this->json(200, $out);
        }

        $out[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        $out[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

        return $this->json(400, $out);
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
                    ->getTheme('theme_admin')
                    ->createBlock('filemanager/modal-file-show_visualize-image.php', $this->pathViews)
                    ->addVars([ 'path' => $path ]);
        }
        if (in_array($info[ 'ext' ], self::$extensionCode)) {
            $code = file_get_contents($path);

            return self::template()
                    ->getTheme('theme_admin')
                    ->createBlock('filemanager/modal-file-show_visualize-code.php', $this->pathViews)
                    ->addVars([
                        'code'      => htmlspecialchars($code),
                        'extension' => $info[ 'ext' ]
            ]);
        }
        if (in_array($info[ 'ext' ], self::$extensionVideo)) {
            return self::template()
                    ->getTheme('theme_admin')
                    ->createBlock('filemanager/modal-file-show_visualize-video.php', $this->pathViews)
                    ->addVars([
                        'path'      => $path,
                        'extension' => $info[ 'ext' ]
            ]);
        }
        if (in_array($info[ 'ext' ], self::$extensionAudio)) {
            return self::template()
                    ->getTheme('theme_admin')
                    ->createBlock('filemanager/modal-file-show_visualize-audio.php', $this->pathViews)
                    ->addVars([
                        'path'      => $path,
                        'extension' => $info[ 'ext' ]
            ]);
        }
        if ($out = $this->container->callHook('filemanager.visualize', [ $info[ 'ext' ] ])) {
            return $out;
        }

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-file-show_visualize-default.php', $this->pathViews)
                ->addVars([ 'extension' => $info[ 'ext' ] ]);
    }
}
