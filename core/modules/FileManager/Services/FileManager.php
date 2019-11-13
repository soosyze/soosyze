<?php

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

class FileManager
{
    protected static $whiteList = [
        '7z',
        'ai', 'avi',
        'css', 'csv',
        'doc', 'docx',
        'eps',
        'file',
        'gif', 'gzip',
        'html',
        'ico',
        'jpeg', 'jpg', 'json',
        'mp3', 'mp4', 'mpeg',
        'odp', 'ods', 'odt',
        'pdf', 'png', 'ppt', 'pptx',
        'rar',
        'svg',
        'tar', 'txt',
        'xhtml', 'xls', 'xlsx', 'xml',
        'zip'
    ];

    /**
     * @var \Soosyze\App
     */
    private $core;

    /**
     * @var \Soosyze\Router
     */
    private $router;

    /**
     * @var \SoosyzeCore\FileManager\Services\HookUser
     */
    private $hookUser;

    public function __construct($core, $router, $hookUser)
    {
        $this->core     = $core;
        $this->router   = $router;
        $this->hookUser = $hookUser;
    }

    public static function getWhiteList()
    {
        return self::$whiteList;
    }

    public function getBreadcrumb($path)
    {
        $path     = rtrim($path, '/');
        $nextPath = '';
        foreach (explode('/', $path) as $key => $value) {
            $nextPath .= "/$value";
            if (!$this->hookUser->hookFolderShow($nextPath)) {
                continue;
            }
            $breadcrumb[ $key ] = [
                'title_link' => empty($value)
                ? '<i class="fa fa-home"></i> ' . t('Home')
                : $value,
                'link'       => $this->router->getRoute('filemanager.show', [
                    ':path' => Util::cleanPath($nextPath)
                ]),
                'active'     => ''
            ];
        }
        $breadcrumb[ $key ][ 'active' ] = 'active';

        return $breadcrumb;
    }

    public function parseDir(\SplFileInfo $dir, $path, $name = '')
    {
        $info = $this->parseRecursive($dir->getPathname());

        return [
            'ext'        => 'dir',
            'link_show'  => $this->router->getRoute('filemanager.show', [
                ':path' => Util::cleanPath("$path/" . $dir->getBasename())
            ]),
            'name'       => $dir->getBasename(),
            'path'       => $dir->getPath(),
            'size'       => Util::strFileSizeFormatted($info[ 'size' ]),
            'size_octet' => $info[ 'size' ],
            'time'       => strftime('%d/%m/%Y %H:%M', $info[ 'time' ]),
            'type'       => 'dir',
            'actions'    => $this->getActionsFolder($dir, $path, $name)
        ];
    }

    public function parseFile(\SplFileInfo $file, $path)
    {
        $path = Util::cleanPath("$path");

        return [
            'ext'        => $file->getExtension(),
            'link_show'  => $this->router->getRoute('filemanager.file.show', [
                ':path' => $path,
                ':name' => '/' . $file->getBasename('.' . $file->getExtension()),
                ':ext'  => '.' . $file->getExtension()
            ]),
            'name'       => $file->getBasename('.' . $file->getExtension()),
            'path'       => $path,
            'size'       => Util::strFileSizeFormatted($file->getSize()),
            'size_octet' => $file->getSize(),
            'time'       => strftime('%d/%m/%Y %H:%M', $file->getMTime()),
            'type'       => 'file',
            'actions'    => $this->getActionsFile($file, $path)
        ];
    }

    public function getActionsFolder(\SplFileInfo $dir, $path, $name = '')
    {
        $actions = [];
        if ($this->hookUser->hookFolderUpdate($path)) {
            $actions[] = [
                'type'       => 'button',
                'icon'       => 'fa fa-edit',
                'class'      => 'mod',
                'title_link' => t('Rename'),
                'link'       => $this->router->getRoute('filemanager.folder.edit', [
                    ':path' => "$path$name"
                ])
            ];
        }
        if ($this->hookUser->hookFolderDelete($path)) {
            $actions[] = [
                'type'       => 'button',
                'icon'       => 'fa fa-times',
                'class'      => 'mod',
                'title_link' => t('Delete'),
                'link'       => $this->router->getRoute('filemanager.folder.remove', [
                    ':path' => "$path$name"
                ])
            ];
        }

        return $actions;
    }

    public function getActionsFile(\SplFileInfo $file, $path)
    {
        $actions = [];
        $name    = '/' . $file->getBasename('.' . $file->getExtension());
        $ext     = $file->getExtension();
        if ($this->hookUser->hookFileUpdate($path, $name, $ext)) {
            $actions[] = [
                'type'       => 'button',
                'icon'       => 'fa fa-edit',
                'class'      => 'mod',
                'title_link' => t('Rename'),
                'link'       => $this->router->getRoute('filemanager.file.edit', [
                    ':path' => $path,
                    ':name' => $name,
                    ':ext'  => '.' . $ext
                ])
            ];
        }
        if ($this->hookUser->hookFileDelete($path, $name, $ext)) {
            $actions[] = [
                'type'       => 'button',
                'icon'       => 'fa fa-times',
                'class'      => 'mod',
                'title_link' => t('Delete'),
                'link'       => $this->router->getRoute('filemanager.file.remove', [
                    ':path' => $path,
                    ':name' => $name,
                    ':ext'  => '.' . $ext
                ])
            ];
        }
        if ($this->hookUser->hookFileShow($path, $name, $ext)) {
            $actions[] = [
                'type'       => 'button',
                'icon'       => 'far fa-eye',
                'class'      => 'mod',
                'title_link' => t('View'),
                'link'       => $this->router->getRoute('filemanager.file.show', [
                    ':path' => $path,
                    ':name' => $name,
                    ':ext'  => '.' . $ext
                ])
            ];
        }
        if ($this->hookUser->hookFileDownlod($path, $name, $ext)) {
            $actions[] = [
                'type'       => 'link',
                'icon'       => 'fa fa-download',
                'class'      => '',
                'title_link' => t('Download'),
                'link'       => $this->router->getRoute('filemanager.file.download', [
                    ':path' => $path,
                    ':name' => $name,
                    ':ext'  => '.' . $ext
                ])
            ];
        }
        if ($this->hookUser->hookFileCopyClipboard($path, $name, $ext)) {
            $actions[] = [
                'type'       => 'button',
                'icon'       => 'fa fa-copy',
                'class'      => 'copy-clipboard',
                'title_link' => t('Copy'),
                'link'       => $this->core->getPath('files_public') . '/' . $path . $file->getFilename()
            ];
        }

        return $actions;
    }

    public function parseRecursive($dir)
    {
        $dir_iterator = new \RecursiveDirectoryIterator($dir);
        $iterator     = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        $size         = 0;
        $time         = 0;
        foreach ($iterator as $file) {
            /* Fichier instance de SplFileInfo */
            $size += $file->getSize();
            if ($file->getMTime() > $time) {
                $time = $file->getMTime();
            }
        }

        return [ 'size' => $size, 'time' => $time ];
    }
}
