<?php

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

class FileManager
{
    private static $extAllowed = [
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
     * @var \SoosyzeCore\FileManager\Hook\User
     */
    private $hookUser;

    /**
     * @var string
     */
    private $pathViews;

    /**
     * @var \Soosyze\Components\Router\Router
     */
    private $router;

    public function __construct($core, $hookUser, $router)
    {
        $this->core     = $core;
        $this->hookUser = $hookUser;
        $this->router   = $router;

        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public static function getExtAllowed()
    {
        return self::$extAllowed;
    }

    public function getBreadcrumb($path, $keyRoute = 'filemanager.show')
    {
        $path     = rtrim($path, '/');
        $nextPath = '';
        $breadcrumb = [];
        foreach (explode('/', $path) as $key => $value) {
            $nextPath .= "/$value";
            if (!$this->hookUser->hookFolderShow($nextPath)) {
                continue;
            }

            $breadcrumb[ $key ] = [
                'title_link' => empty($value)
                ? '<i class="fa fa-home" aria-hidden="true"></i> ' . t('Home')
                : $value,
                'link'       => $this->router->getRoute($keyRoute, [
                    ':path' => Util::cleanPath($nextPath)
                ]),
                'active'     => ''
            ];
        }
        if (isset($breadcrumb[ $key ])) {
            $breadcrumb[$key][ 'active' ] = 'active';
        }

        return $breadcrumb;
    }

    public function parseDir(\SplFileInfo $dir, $path, $name = '', $keyRoute = 'filemanager.show')
    {
        $info = self::parseRecursive($dir->getPathname());

        return [
            'ext'        => 'dir',
            'link_show'  => $this->router->getRoute($keyRoute, [
                ':path' => Util::cleanPath("$path/" . $dir->getBasename())
            ]),
            'name'       => $dir->getBasename(),
            'path'       => $dir->getPath(),
            'size'       => Util::strFileSizeFormatted($info[ 'size' ], 2, t('Empty folder')),
            'size_octet' => $info[ 'size' ],
            'time'       => strftime('%d/%m/%Y %H:%M', $info[ 'time' ]
                ? $info[ 'time' ]
                : $dir->getMTime()),
            'type'       => 'dir',
            'actions'    => $this->getActionsFolder($path, $name)
        ];
    }

    public function parseFile(\SplFileInfo $file, $path)
    {
        $path = Util::cleanPath($path);

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
            'type'       => in_array($file->getExtension(), [
                'gif', 'ico', 'jpg', 'jpeg', 'png'
                ]) ? 'image' : 'file',
            'link' => $this->core->getPath('files_public') . $path . '/' . $file->getFilename(),
            'actions'    => $this->getActionsFile($file, $path)
        ];
    }

    public function getActionsFolder($path, $name = '')
    {
        $actions = [];
        if ($this->hookUser->hookFolderUpdate("$path$name")) {
            $actions[] = [
                'class'      => 'mod',
                'icon'       => 'fa fa-edit',
                'key'        => 'filemanager.folder.edit',
                'link'       => $this->router->getRoute('filemanager.folder.edit', [
                    ':path' => "$path$name"
                ]),
                'title_link' => t('Rename')
            ];
        }
        if ($this->hookUser->hookFolderDelete("$path$name")) {
            $actions[] = [
                'class'      => 'mod',
                'icon'       => 'fa fa-times',
                'key'        => 'filemanager.folder.remove',
                'link'       => $this->router->getRoute('filemanager.folder.remove', [
                    ':path' => "$path$name"
                ]),
                'title_link' => t('Delete')
            ];
        }

        return $actions;
    }

    public function getFileSubmenu($keyRoute, \SplFileInfo $file, $path)
    {
        $menu = $this->getActionsFile($file, $path);

        $this->core->callHook('filemanager.file.submenu', [ &$menu ]);

        return $this->core
                ->get('template')
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-submenu.php', $this->pathViews)
                ->addVars([
                    'key_route' => $keyRoute,
                    'menu'      => $menu
        ]);
    }

    public function getFolderSubmenu($keyRoute, $path)
    {
        $menu = $this->getActionsFolder($path);

        $this->core->callHook('filemanager.folder.submenu', [ &$menu ]);

        return $this->core
                ->get('template')
                ->getTheme('theme_admin')
                ->createBlock('filemanager/modal-submenu.php', $this->pathViews)
                ->addVars([
                    'key_route' => $keyRoute,
                    'menu'      => $menu
        ]);
    }

    public function getActionsFile(\SplFileInfo $file, $path)
    {
        $actions = [];
        $name    = '/' . $file->getBasename('.' . $file->getExtension());
        $ext     = $file->getExtension();

        if ($this->hookUser->hookFileShow($path, $name, $ext)) {
            $actions[] = [
                'class'      => 'mod',
                'icon'       => 'far fa-eye',
                'key'        => 'filemanager.file.show',
                'link'       => $this->router->getRoute('filemanager.file.show', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => 'View',
                'type'       => 'button'
            ];
        }
        if ($this->hookUser->hookFileUpdate($path, $name, $ext)) {
            $actions[] = [
                'class'      => 'mod',
                'icon'       => 'fa fa-edit',
                'key'        => 'filemanager.file.edit',
                'link'       => $this->router->getRoute('filemanager.file.edit', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => 'Rename',
                'type'       => 'button'
            ];
        }
        if ($this->hookUser->hookFileDelete($path, $name, $ext)) {
            $actions[] = [
                'class'      => 'mod',
                'icon'       => 'fa fa-times',
                'key'        => 'filemanager.file.remove',
                'link'       => $this->router->getRoute('filemanager.file.remove', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => 'Delete',
                'type'       => 'button'
            ];
        }
        if ($this->hookUser->hookFileDownlod($path, $name, $ext)) {
            $actions[] = [
                'class'      => '',
                'icon'       => 'fa fa-download',
                'key'        => 'filemanager.file.download',
                'link'       => $this->router->getRoute('filemanager.file.download', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => 'Download',
                'type'       => 'link'
            ];
        }
        if ($this->hookUser->hookFileCopyClipboard($path, $name, $ext)) {
            $actions[] = [
                'class'      => 'copy-clipboard',
                'icon'       => 'fa fa-copy',
                'key'        => '',
                'link'       => $this->core->getPath('files_public') . $path . '/' . $file->getFilename(),
                'title_link' => 'Copy link',
                'type'       => 'button'
            ];
        }
        if ($this->hookUser->hookFileCopy($path, $name, $ext)) {
            $actions[] = [
                'class'      => 'mod',
                'icon'       => 'fa fa-copy',
                'key'        => 'filemanager.copy.admin',
                'link'       => $this->router->getRoute('filemanager.copy.admin', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => 'Deplace or copy',
                'type'       => 'button'
            ];
        }

        return $actions;
    }

    public static function parseRecursive($dir)
    {
        $dirIterator = new \RecursiveDirectoryIterator($dir);
        $iterator    = new \RecursiveIteratorIterator($dirIterator);

        $size = 0;
        $time = 0;

        $iterator->rewind();
        foreach ($iterator as $file) {
            if ($iterator->isDot() || $iterator->isLink()) {
                continue;
            }
            /* Fichier instance de SplFileInfo */
            $size += $file->getSize();
            if ($file->getMTime() > $time) {
                $time = $file->getMTime();
            }
        }

        return [ 'size' => $size, 'time' => $time ];
    }
}
