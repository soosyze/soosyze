<?php

namespace SoosyzeCore\FileSystem\Services;

use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class File
{
    protected $core;

    public function __construct($core)
    {
        $this->core = $core;
    }

    public function inputFile($name, FormBuilder &$form, $content = '')
    {
        $attr = [
            'class'    => 'btn btn-danger form-file-reset',
            'onclick'  => "document.getElementById('file-image-$name').style.display='none';"
            . "document.getElementById('$name').value='';"
            . "document.getElementById('file-name-$name').value='';"
            . "document.getElementById('file-reset-$name').disabled = true;",
            '_content' => '✗',
            'id'       => "file-reset-$name",
            'type'     => 'button'
        ];
        if (!empty($content)) {
            $form->group("file-image-$name-group", 'div', function ($form) use ($name, $content) {
                $form->html("file-image-$name", '<img:css:attr/>', [
                    'src'   => $content,
                    'class' => 'input-file-img img-thumbnail'
                ]);
            }, [ 'class' => 'form-group' ]);
        } else {
            $attr[ 'disabled' ] = 'disabled';
        }

        $form->group("file-input-$name-group", 'div', function ($form) use ($name, $content, $attr) {
            $form->file($name, [
                    'style'    => 'display:none',
                    'onchange' => "document.getElementById('file-name-$name').value = this.files[0].name;"
                    . "document.getElementById('file-reset-$name').disabled = false;"
                ])
                ->text("file-name-$name", [
                    'class'   => 'form-control form-file-name',
                    'onclick' => "document.getElementById('$name').click();",
                    'value'   => $content
                ])
                ->html("file-reset-$name", '<button:css:attr>:_content</button>', $attr);
        }, [ 'class' => 'form-group-flex' ]);
    }

    public function validImage($name, Validator &$validator)
    {
        $validator->addIntput("file-reset-$name", '');
    }

    public function cleanPath($path, UploadedFileInterface $file)
    {
        $name = $file->getClientFilename();

        return $path . Util::DS . Util::strSlug($name);
    }

    public function cleanPathAndMoveTo($path, UploadedFileInterface $file)
    {
        $targetPath = $this->cleanPath($path, $file);
        $file->moveTo($targetPath);

        return $targetPath;
    }

    public function add($file, $fileHidden = '')
    {
        $this->file        = $file;
        $this->file_hidden = $fileHidden;

        return $this;
    }

    public function moveTo($name, $path = null)
    {
        $this->name = Util::strSlug($name);
        $this->path = $path === null
            ? $this->core->getSetting('files_public', 'app/files')
            : $path;

        return $this;
    }

    public function callGet(callable $callback)
    {
        $this->call_get = $callback;

        return $this;
    }

    public function callMove(callable $callback)
    {
        $this->call_move = $callback;

        return $this;
    }

    public function callDelete(callable $callback)
    {
        $this->call_delete = $callback;

        return $this;
    }

    public function save()
    {
        if (!($this->file instanceof UploadedFileInterface)) {
            return;
        }
        if ($this->file->getError() === UPLOAD_ERR_OK) {
            $filename = $this->file->getClientFilename();
            $ext      = Util::getFileExtension($filename);

            $move = "$this->path/$this->name.$ext";
            $this->file->moveTo($move);
            call_user_func_array($this->call_move, [ $this->name, $move ]);
        } elseif ($this->file->getError() === UPLOAD_ERR_NO_FILE) {
            $file = call_user_func_array($this->call_get, [ $this->name ]);
            if (empty($this->file_hidden) && $file) {
                call_user_func_array($this->call_delete, [ $this->name, $file ]);
                unlink($file);
            }
        }
    }
    
    public function saveOne()
    {
        if (!($this->file instanceof UploadedFileInterface)) {
            return;
        }
        if ($this->file->getError() === UPLOAD_ERR_OK) {
            $ClientFilename = $this->file->getClientFilename();
            $ext            = Util::getFileExtension($ClientFilename);
            $filename       = pathinfo($ClientFilename, PATHINFO_FILENAME);
            $name           = Util::strSlug($filename);

            $move = "$this->path/$name.$ext";
            $this->file->moveTo($move);
        }
    }
}
