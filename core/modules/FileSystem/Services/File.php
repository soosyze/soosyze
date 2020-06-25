<?php

namespace SoosyzeCore\FileSystem\Services;

use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class File
{
    /**
     * @var \Soosyze\App
     */
    protected $core;

    /**
     * Le fichier à déplacer.
     *
     * @var UploadedFileInterface
     */
    protected $file;

    /**
     * Le champ de fichier caché.
     *
     * @var string
     */
    protected $fileHidden;

    /**
     * Le nom du fichier à déplacer.
     *
     * @var string
     */
    protected $name;

    /**
     * L'extension du fichier a déplacer.
     *
     * @var string
     */
    protected $ext;

    /**
     * Droits attribués à la création du répertoire.
     *
     * @var int
     */
    protected $mode = 0755;

    /**
     * Si le répertoire doit-être corrigé.
     *
     * @var bool
     */
    protected $resolveDir = false;

    /**
     * Si le nom de fichier doit-être corrigé.
     *
     * @var string
     */
    protected $resolveName = false;

    /**
     * Le répertoire d'envoie.
     *
     * @var string
     */
    protected $dir = null;

    protected $basePath = '';

    protected $callGet = null;
    
    protected $callDelete = null;

    protected $callMove = null;

    public function __construct($core)
    {
        $this->core     = $core;
        $this->basePath = $this->core->getRequest()->getBasePath();
        $this->dir      = $this->core->getDir('files_public', 'app/files');
    }

    public function inputFile($name, &$form, $content = '', $type = 'image')
    {
        $attr = [
            'class'      => 'btn btn-danger form-file-reset',
            'onclick'    => "document.getElementById('file-image-$name').style.display='none';"
            . "document.getElementById('$name').value='';"
            . "document.getElementById('file-name-$name').value='';"
            . "document.getElementById('file-reset-$name').disabled = true;",
            '_content'   => '✗',
            'id'         => "file-reset-$name",
            'type'       => 'button',
            'aria-label' => 'Supprimer le fichier'
        ];

        $src = is_file($this->core->getSetting('root', '') . $content)
            ? $this->basePath . $content
            : '';

        if (empty($src)) {
            $attr[ 'disabled' ] = 'disabled';
        } elseif ($type == 'image') {
            $form->group("file-image-$name-group", 'div', function ($form) use ($name, $src) {
                $form->html("file-image-$name", '<img:attr/>', [
                    'alt'   => 'Picture user',
                    'src'   => $src,
                    'class' => 'input-file-img img-thumbnail'
                ]);
            }, [ 'class' => 'form-group' ]);
        } else {
            $form->group("file-$name-group", 'div', function ($form) use ($name, $src) {
                $form->html("file-image-$name", '<a:attr/><i class="fa fa-download"></i> :_content</a>', [
                    'href'     => $src,
                    '_content' => $src
                ]);
            }, [ 'class' => 'form-group' ]);
        }

        $form->group("file-input-$name-flex", 'div', function ($form) use ($name, $content, $attr) {
            $form
                ->text("file-name-$name", [
                    'aria-label' => 'visualisation du chemin du fichier',
                    'class'      => 'form-control form-file-name',
                    'onclick'    => "document.getElementById('$name').click();",
                    'value'      => $content
                ])
                ->html("file-reset-$name", '<button:attr>:_content</button>', $attr)
                ->file($name, [
                    'style'    => 'display:none',
                    'onchange' => "document.getElementById('file-name-$name').value = this.files[0].name;"
                    . "document.getElementById('file-reset-$name').disabled = false;"
            ]);
        }, [ 'class' => 'form-group-flex' ]);
    }

    public function validImage($name, Validator &$validator)
    {
        $validator->addIntput("file-reset-$name", '');
    }

    public function add(UploadedFileInterface $file, $fileHidden = '')
    {
        $clone             = clone $this;
        $clone->file       = $file;
        $clone->fileHidden = $fileHidden;
        $ClientFilename    = $file->getClientFilename();
        $clone->ext        = Util::getFileExtension($ClientFilename);
        $name              = pathinfo($ClientFilename, PATHINFO_FILENAME);
        $clone->name       = Util::strSlug($name);

        return $clone;
    }

    public function setName($name)
    {
        $clone       = clone $this;
        $clone->name = Util::strSlug($name);

        return $clone;
    }

    public function setPath($path = null)
    {
        $clone      = clone $this;
        $clone->dir = $path === null
            ? $this->core->getSettingEnv('files_public', 'app/files')
            : $path;

        return $clone;
    }

    public function setBasePath($basePath = null)
    {
        $clone           = clone $this;
        $clone->basePath = $basePath === null
            ? $this->core->getRequest()->getBasePath()
            : $basePath;

        return $clone;
    }

    public function setResolvePath($resolve = true, $mode = 0755)
    {
        $clone             = clone $this;
        $clone->resolveDir = $resolve;
        $clone->mode       = $mode;

        return $clone;
    }

    public function setResolveName($resolve = true)
    {
        $clone              = clone $this;
        $clone->resolveName = $resolve;

        return $clone;
    }

    public function callGet(callable $callback)
    {
        $clone          = clone $this;
        $clone->callGet = $callback;

        return $clone;
    }

    public function callMove(callable $callback)
    {
        $clone           = clone $this;
        $clone->callMove = $callback;

        return $clone;
    }

    public function callDelete(callable $callback)
    {
        $clone             = clone $this;
        $clone->callDelete = $callback;

        return $clone;
    }

    public function save()
    {
        if (!($this->file instanceof UploadedFileInterface)) {
            return;
        }
        if ($this->file->getError() === UPLOAD_ERR_OK) {
            $this->resolveDir();
            $move = $this->resolveName();

            $this->file->moveTo($move);
            call_user_func_array($this->callMove, [ $this->name, "{$this->name}.{$this->ext}",
                $move ]);
        } elseif ($this->file->getError() === UPLOAD_ERR_NO_FILE) {
            $file = call_user_func_array($this->callGet, [ $this->name, "{$this->name}.{$this->ext}" ]);
            if (empty($this->fileHidden) && $file) {
                call_user_func_array($this->callDelete, [ $this->name, "{$this->name}.{$this->ext}",
                    $file ]);
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function saveOne()
    {
        if (!($this->file instanceof UploadedFileInterface)) {
            return '';
        }
        if ($this->file->getError() === UPLOAD_ERR_OK) {
            $this->resolveDir();
            $move = $this->resolveName();
            $this->file->moveTo($move);
            if ($this->callMove) {
                call_user_func_array($this->callMove, [
                    $this->name, "{$this->name}.{$this->ext}", $move
                ]);
            }

            return $move;
        }
    }

    protected function resolveDir()
    {
        if ($this->resolveDir && !is_dir($this->dir)) {
            mkdir($this->dir, $this->mode, true);
        }
    }

    protected function resolveName()
    {
        $file = "{$this->dir}/{$this->name}.{$this->ext}";
        if (!$this->resolveName || !is_file($file)) {
            return $file;
        }
        $i = 1;
        while (is_file("{$this->dir}/{$this->name}_{$i}.{$this->ext}")) {
            $i++;
        }

        return "{$this->dir}/{$this->name}_{$i}.{$this->ext}";
    }
}
