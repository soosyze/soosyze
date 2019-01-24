<?php

namespace FileSystem\Services;

use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

class File
{
    public function formFile($name, FormBuilder &$form, $content = '')
    {
        $attr = [
            'class'   => 'btn btn-danger form-file-reset',
            'onclick' => "document.getElementById('file-image-$name').style.display='none';"
            . "document.getElementById('file-$name').value='';"
            . "document.getElementById('file-name-$name').value='';"
            . "document.getElementById('file-reset-$name').disabled = true;",
            'value'   => '✗'
        ];
        if (!empty($content)) {
            $form->group("file-image-$name-group", 'div', function ($form) use ($name, $content) {
                $form->html("file-image-$name", '<img:css:attr/>', [
                    'src'   => $content,
                    'id'    => "file-image-$name",
                    'class' => 'img-thumbnail'
                ]);
            }, [ 'class' => 'form-group' ]);
        } else {
            $attr[ 'disabled' ] = 'disabled';
        }

        $form->group("file-input-$name-group", 'div', function ($form) use ($name, $content, $attr) {
            $form->file($name, "file-$name", [
                    'style'    => 'display:none',
                    'onchange' => "document.getElementById('file-name-$name').value = this.files[0].name;"
                    . "document.getElementById('file-reset-$name').disabled = false;"
                ])
                ->text("file-name-$name", "file-name-$name", [
                    'class'   => 'form-control form-file-name',
                    'onclick' => "document.getElementById('file-$name').click();",
                    'value'   => $content
                ])->button("file-reset-$name", "file-reset-$name", $attr);
        }, [ 'class' => 'form-group' ]);
    }

    public function validImage($name, Validator &$validator)
    {
        $validator->addIntput("file-reset-$name", '');
    }

    public function cleanPath($path, UploadedFileInterface $file)
    {
        $name = strtolower($file->getClientFilename());

        $name = strtr($name, 'àáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 'aaaaaaceeeeiiiioooooouuuuyy');
        $name = str_replace(' ', '_', $name);
        $name = preg_replace('/([^.a-z0-9_]+)/i', '-', $name);

        return $path . Util::DS . $name;
    }

    public function cleanPathAndMoveTo($path, UploadedFileInterface $file)
    {
        $targetPath = $this->cleanPath($path, $file);

        $file->moveTo($targetPath);

        return $targetPath;
    }
}
