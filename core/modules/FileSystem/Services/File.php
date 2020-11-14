<?php

namespace SoosyzeCore\FileSystem\Services;

use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Util\Util;

class File
{
    /**
     * Racine du l'URL.
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * Fonction de suppression des données du fichier.
     *
     * @var callable|null
     */
    protected $callDelete = null;

    /**
     * Fonction de récupération des données du fichier.
     *
     * @var callable|null
     */
    protected $callGet = null;
    
    /**
     * Fonction de déplacement des données du fichier.
     *
     * @var callable|null
     */
    protected $callMove = null;

    /**
     * Le répertoire d'envoie.
     *
     * @var string
     */
    protected $dir = null;

    /**
     * L'extension du fichier a déplacer.
     *
     * @var string
     */
    protected $ext;

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
     * Si le répertoire doit-être corrigé.
     *
     * @var bool
     */
    protected $isResolveDir = false;

    /**
     * Droits attribués à la création du répertoire.
     *
     * @var int
     */
    protected $mode = 0755;

    /**
     * Le nom du fichier à déplacer.
     *
     * @var string
     */
    protected $name;

    /**
     * Le nouveau nom du fichier à déplacer si corrigé.
     *
     * @var string
     */
    protected $nameResolved;

    /**
     * Le chemin d'envoie.
     *
     * @var string
     */
    protected $path = null;
    
    /**
     * Le répertoire racine.
     *
     * @var string
     */
    protected $root;

    /**
     * @param \Soosyze\App $core
     */
    public function __construct($core)
    {
        $this->basePath = $core->getRequest()->getBasePath();
        $this->dir      = $core->getDir('files_public', 'app/files');
        $this->path     = $core->getSettingEnv('files_public', 'app/files');
        $this->root     = $core->getSetting('root', '');
    }

    public function inputFile($name, &$form, $filePath = '', $type = 'image')
    {
        $this->getThumbnail($name, $form, $filePath, $type);

        $form->group("file-$name-flex", 'div', function ($form) use ($name, $filePath) {
            $attr = [
                'class'      => 'btn btn-danger form-file-reset',
                'onclick'    => "document.getElementById('file-$name-thumbnail') !== null"
                . "? document.getElementById('file-$name-thumbnail').style.display='none'"
                . ': \'\';'
                . "document.getElementById('$name').value='';"
                . "document.getElementById('file-$name-name').value='';"
                . "document.getElementById('file-$name-reset').disabled = true;",
                '_content'   => '<i class="fa fa-times" aria-hidden="true"></i>',
                'id'         => "file-$name-reset",
                'type'       => 'button',
                'aria-label' => 'Supprimer le fichier',
                'disabled'   => empty($filePath)
            ];

            $form
                ->text("file-$name-name", [
                    'aria-label' => t('View the file path'),
                    'class'      => 'form-control form-file-name',
                    'onclick'    => "document.getElementById('$name').click();",
                    'value'      => $filePath
                ])
                ->file($name, [
                    'style'    => 'display:none',
                    'onchange' => "document.getElementById('file-$name-name').value = this.files[0].name;"
                    . "document.getElementById('file-$name-reset').disabled = false;"
                ])
                ->html("file-$name-reset", '<button:attr>:_content</button>', $attr);
        }, [ 'class' => 'form-group-flex' ]);
    }

    public function add(UploadedFileInterface $file, $fileHidden = '')
    {
        $clone             = clone $this;
        $clone->file       = $file;
        $clone->fileHidden = $fileHidden;
        $clone->ext        = Util::getFileExtension($file->getClientFilename());
        $clone->name       = Util::strSlug(pathinfo($file->getClientFilename(), PATHINFO_FILENAME));

        return $clone;
    }

    public function setName($name)
    {
        $clone       = clone $this;
        $clone->name = Util::strSlug($name);

        return $clone;
    }

    public function setPath($path)
    {
        $clone       = clone $this;
        $clone->dir  .= $path;
        $clone->path .= $path;

        return $clone;
    }

    public function isResolvePath($resolve = true, $mode = 0755)
    {
        $clone               = clone $this;
        $clone->isResolveDir = $resolve;
        $clone->mode         = $mode;

        return $clone;
    }

    public function isResolveName()
    {
        if (!$this->name) {
            throw new \Exception('To resolve the file name, the file must be present.');
        }

        $clone = clone $this;
        $file  = "{$this->dir}/{$this->name}.{$this->ext}";

        if (is_file($file)) {
            $i = 1;
            while (is_file("{$this->dir}/{$this->name}_{$i}.{$this->ext}")) {
                ++$i;
            }
            $clone->nameResolved = "{$this->name}_{$i}";
        }

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
            throw new Exception('A file must be present to be saved.');
        }

        if ($this->file->getError() === UPLOAD_ERR_OK) {
            $this->resolveDir();
            $move = $this->getMoveDir();
            $this->file->moveTo($move);

            call_user_func_array($this->callMove, [
                $this->name, "{$this->name}.{$this->ext}", $this->getMovePath()
            ]);
        } elseif ($this->file->getError() === UPLOAD_ERR_NO_FILE) {
            $file = call_user_func_array($this->callGet, [
                $this->name, "{$this->name}.{$this->ext}"
            ]);

            if (empty($this->fileHidden) && $file) {
                call_user_func_array($this->callDelete, [
                    $this->name, "{$this->name}.{$this->ext}", $file
                ]);

                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        
        return $this;
    }

    public function saveOne()
    {
        if (!($this->file instanceof UploadedFileInterface)) {
            throw new Exception('A file must be present to be saved.');
        }

        if ($this->file->getError() === UPLOAD_ERR_OK) {
            $this->resolveDir();
            $move = $this->getMoveDir();
            $this->file->moveTo($move);

            if ($this->callMove) {
                call_user_func_array($this->callMove, [
                    $this->name, "{$this->name}.{$this->ext}", $this->getMovePath()
                ]);
            }
        }

        return $this;
    }
    
    public function getName()
    {
        return $this->nameResolved
            ? $this->nameResolved
            : $this->name;
    }

    /**
     * Le répertoire du serveur dans lequel les fichiers sont envoyés.
     *
     * @return string
     */
    public function getMoveDir()
    {
        $filename = $this->getName();

        return "{$this->dir}/{$filename}.{$this->ext}";
    }

    /**
     * Le chemin relatif du répertoire dans lequel fichiers sont envoyés.
     *
     * @return string
     */
    public function getMovePath()
    {
        $filename = $this->getName();

        return "{$this->path}/{$filename}.{$this->ext}";
    }

    /**
     * Le chemin absolu du répertoire dans lequel fichiers sont envoyés.
     *
     * @return string
     */
    public function getMovePathAbsolute()
    {
        $filename = $this->getName();

        return "{$this->basePath}{$this->path}/{$filename}.{$this->ext}";
    }

    protected function getThumbnail($name, &$form, $filePath, $type)
    {
        $src = is_file($this->root . $filePath)
            ? $this->basePath . $filePath
            : '';

        if (empty($src)) {
            return;
        }

        $content = $type === 'image'
            ? '<div class="img-thumbnail img-thumbnail-light"><a href="' . $src . '"/><img alt="Thumbnail" src="' . $src . '" class="img-responsive"/></a></div>'
            : '<a href="' . $src . '"/><i class="fa fa-download" aria-hidden="true"></i> ' . $src . '</a>';

        $form->group("file-$name-thumbnail-group", 'div', function ($form) use ($content, $name) {
            $form->html("file-$name-thumbnail", '<div:attr>:_content</div>', [
                '_content' => $content
            ]);
        }, [ 'class' => 'form-group' ]);
    }

    protected function resolveDir()
    {
        if ($this->isResolveDir && !is_dir($this->dir)) {
            mkdir($this->dir, $this->mode, true);
        }
    }
}
