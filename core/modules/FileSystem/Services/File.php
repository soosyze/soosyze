<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\FileSystem\Services;

use Core;
use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Form\FormGroupBuilder;
use Soosyze\Components\Util\Util;

class File
{
    /**
     * Racine du l'URL.
     *
     * @var string
     */
    private $basePath;

    /**
     * Fonction de suppression des données du fichier.
     *
     * @var \Closure|null
     */
    private $callDelete;

    /**
     * Fonction de récupération des données du fichier.
     *
     * @var \Closure|null
     */
    private $callGet;

    /**
     * Fonction de déplacement des données du fichier.
     *
     * @var \Closure|null
     */
    private $callMove;

    /**
     * Le répertoire d'envoie.
     *
     * @var string
     */
    private $dir;

    /**
     * L'extension du fichier a déplacer.
     *
     * @var string
     */
    private $ext;

    /**
     * Le fichier à déplacer.
     *
     * @var UploadedFileInterface
     */
    private $uploadedFile;

    /**
     * Le champ de fichier caché.
     *
     * @var string
     */
    private $hiddenFilename;

    /**
     * Si le répertoire doit-être corrigé.
     *
     * @var bool
     */
    private $isResolveDir = false;

    /**
     * Droits attribués à la création du répertoire.
     *
     * @var int
     */
    private $mode = 0755;

    /**
     * Le nom du fichier à déplacer.
     *
     * @var string
     */
    private $name;

    /**
     * Le nouveau nom du fichier à déplacer si corrigé.
     *
     * @var string|null
     */
    private $nameResolved;

    /**
     * Le chemin d'envoie.
     *
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $randomPrefix;

    /**
     * Le répertoire racine.
     *
     * @var string
     */
    private $root;

    public function __construct(Core $core, string $root)
    {
        $this->basePath = $core->getRequest()->getBasePath();
        $this->dir      = $core->getDir('files_public', 'app/files');
        $this->path     = $core->getSettingEnv('files_public', 'app/files');
        $this->root     = $root;
    }

    public function inputFile(
        string $name,
        FormGroupBuilder &$form,
        ?string $filePath = '',
        string $type = 'image'
    ): void {
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
                ':content'   => '<i class="fa fa-times" aria-hidden="true"></i>',
                'id'         => "file-$name-reset",
                'type'       => 'button',
                'aria-label' => t('Delete file'),
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
                ->html("file-$name-reset", '<button:attr>:content</button>', $attr);
        }, [ 'class' => 'form-group-flex' ]);
    }

    public function add(
        UploadedFileInterface $uploadedFile,
        string $hiddenFilename = ''
    ): self {
        $clientFilename = $uploadedFile->getClientFilename() ?? '';

        $clone                 = clone $this;
        $clone->uploadedFile   = $uploadedFile;
        $clone->hiddenFilename = $hiddenFilename;
        $clone->ext            = Util::getFileExtension($clientFilename);
        $clone->name           = Util::strSlug(pathinfo($clientFilename, PATHINFO_FILENAME));

        return $clone;
    }

    public function setName(string $name): self
    {
        $clone       = clone $this;
        $clone->name = Util::strSlug($name);

        return $clone;
    }

    public function setPath(string $path): self
    {
        $clone       = clone $this;
        $clone->dir  .= $path;
        $clone->path .= $path;

        return $clone;
    }

    public function isResolvePath(bool $isResolveDir = true, int $mode = 0755): self
    {
        $clone               = clone $this;
        $clone->isResolveDir = $isResolveDir;
        $clone->mode         = $mode;

        return $clone;
    }

    public function isResolveName(): self
    {
        if (
            !$this->name &&
            (!($this->uploadedFile instanceof UploadedFileInterface) ||
            $this->uploadedFile->getError() !== UPLOAD_ERR_NO_FILE)
        ) {
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

    public function withRandomPrefix(): self
    {
        $this->randomPrefix = Util::strRandom(10, '0123456789') . '-' . Util::strRandom(5, '0123456789');

        return $this;
    }

    public function callGet(\Closure $closure): self
    {
        $clone          = clone $this;
        $clone->callGet = $closure;

        return $clone;
    }

    public function callMove(\Closure $closure): self
    {
        $clone           = clone $this;
        $clone->callMove = $closure;

        return $clone;
    }

    public function callDelete(\Closure $closure): self
    {
        $clone             = clone $this;
        $clone->callDelete = $closure;

        return $clone;
    }

    public function save(): self
    {
        if (!$this->uploadedFile instanceof UploadedFileInterface) {
            throw new \InvalidArgumentException('A file must be present to be saved.');
        }

        if ($this->uploadedFile->getError() === UPLOAD_ERR_OK) {
            $this->moveUploadedFile();
        } elseif ($this->uploadedFile->getError() === UPLOAD_ERR_NO_FILE && empty($this->hiddenFilename)) {
            $filename = $this->getUploadedFilename();
            if ($filename === null) {
                return $this;
            }

            $this->deleteUploadedFile($filename);
        }

        return $this;
    }

    public function saveOne(): self
    {
        if (!$this->uploadedFile instanceof UploadedFileInterface) {
            throw new \Exception('A file must be present to be saved.');
        }

        if ($this->uploadedFile->getError() === UPLOAD_ERR_OK) {
            $this->moveUploadedFile();
        }

        return $this;
    }

    public function getName(): string
    {
        return ($this->randomPrefix ? $this->randomPrefix . '-' : '') . ($this->nameResolved ?? $this->name);
    }

    /**
     * Le répertoire du serveur dans lequel les fichiers sont envoyés.
     */
    public function getMoveDir(): string
    {
        $filename = $this->getName();

        return "{$this->dir}/{$filename}.{$this->ext}";
    }

    /**
     * Le chemin relatif du répertoire dans lequel fichiers sont envoyés.
     */
    public function getMovePath(): string
    {
        $filename = $this->getName();

        return "{$this->path}/{$filename}.{$this->ext}";
    }

    /**
     * Le chemin absolu du répertoire dans lequel fichiers sont envoyés.
     */
    public function getMovePathAbsolute(): string
    {
        $filename = $this->getName();

        return "{$this->basePath}{$this->path}/{$filename}.{$this->ext}";
    }

    private function moveUploadedFile(): void
    {
        $this->resolveDir();
        $move = $this->getMoveDir();

        $this->uploadedFile->moveTo($move);

        if ($this->callMove instanceof \Closure) {
            call_user_func_array(
                $this->callMove,
                [ $this->name, new \SplFileInfo($this->getMovePath()) ]
            );
        }
    }

    private function getUploadedFilename(): ?string
    {
        if (!$this->callGet instanceof \Closure) {
            throw new \InvalidArgumentException('The callback function to get the file must be defined.');
        }

        $file = call_user_func_array($this->callGet, [ $this->name ]);

        if (!is_string($file) && $file !== null) {
            throw new \RuntimeException('The callback function to get the file should return a string.');
        }

        return $file;
    }

    private function deleteUploadedFile(string $filename): void
    {
        if (!$this->callDelete instanceof \Closure) {
            throw new \InvalidArgumentException('The callback function to delete the file must be defined.');
        }

        call_user_func_array(
            $this->callDelete,
            [ $this->name, new \SplFileInfo($filename) ]
        );

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    private function getThumbnail(
        string $name,
        FormGroupBuilder &$form,
        ?string $filePath,
        string $type
    ): void {
        $src = is_file($this->root . $filePath)
            ? $this->basePath . $filePath
            : '';

        if (empty($src)) {
            return;
        }

        $content = $type === 'image'
            ? '<div class="img-thumbnail img-thumbnail-light"><a href="' . $src . '"/><img alt="Thumbnail" src="' . $src . '" class="img-responsive"/></a></div>'
            : '<a href="' . $src . '"/><i class="fa fa-download" aria-hidden="true"></i> ' . $src . '</a>';

        $form->group("file-$name-thumbnail-group", 'div', function ($form) use (
            $content,
            $name
        ) {
            $form->html("file-$name-thumbnail", '<div:attr>:content</div>', [
                ':content' => $content
            ]);
        }, [ 'class' => 'form-group' ]);
    }

    private function resolveDir(): void
    {
        if ($this->isResolveDir && !is_dir($this->dir)) {
            mkdir($this->dir, $this->mode, true);
        }
    }
}
