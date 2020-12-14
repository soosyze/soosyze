<?php

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

class FileManager
{
    protected static $extAllowed = [
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
     * @var \SoosyzeCore\FileManager\Services\HookUser
     */
    private $hookUser;

    private $pathViews;

    /**
     * @var \Soosyze\Router
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
                'icon'       => 'fa fa-edit',
                'class'      => 'mod',
                'title_link' => t('Rename'),
                'link'       => $this->router->getRoute('filemanager.folder.edit', [
                    ':path' => "$path$name"
                ])
            ];
        }
        if ($this->hookUser->hookFolderDelete("$path$name")) {
            $actions[] = [
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

    public function getFileSubmenu($keyRoute, \SplFileInfo $file, $path)
    {
        $name = '/' . $file->getBasename('.' . $file->getExtension());
        $ext  = $file->getExtension();

        $menu = [
            [
                'class'      => 'mod',
                'key'        => 'filemanager.file.show',
                'request'    => $this->router->getRequestByRoute('filemanager.file.show', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => t('View')
            ], [
                'class'      => 'mod',
                'key'        => 'filemanager.file.edit',
                'request'    => $this->router->getRequestByRoute('filemanager.file.edit', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => t('Rename')
            ], [
                'class'      => 'mod',
                'key'        => 'filemanager.file.remove',
                'request'    => $this->router->getRequestByRoute('filemanager.file.remove', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => t('Delete')
            ], [
                'class'      => '',
                'key'        => 'filemanager.file.download',
                'request'    => $this->router->getRequestByRoute('filemanager.file.download', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => t('Download')
            ], [
                'class'      => 'mod',
                'key'        => 'filemanager.file.copy',
                'request'    => $this->router->getRequestByRoute('filemanager.copy.admin', [
                    ':path' => $path, ':name' => $name, ':ext'  => '.' . $ext
                ]),
                'title_link' => t('Deplace or copy')
            ],
        ];

        if ($this->hookUser->hookFileCopyClipboard($path, $name, $ext)) {
            $menu[] = [
                'class'      => 'copy-clipboard',
                'key'        => '',
                'link'       => $this->core->getPath('files_public') . $path . '/' . $file->getFilename(),
                'title_link' => t('Copy link')
            ];
        }

        $this->core->callHook('filemanager.file.submenu', [ &$menu ]);

        foreach ($menu as $key => &$link) {
            if (isset($link[ 'link' ])) {
                continue;
            }
            if (!$this->core->callHook('app.granted.route', [ $link[ 'request' ] ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = $link[ 'request' ]->getUri();
        }
        unset($link);

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
        $menu = [
            [
                'key'        => 'filemanager.folder.edit',
                'request'    => $this->router->getRequestByRoute('filemanager.folder.edit', [
                    ':path' => $path
                ]),
                'title_link' => t('Rename')
            ], [
                'key'        => 'filemanager.folder.remove',
                'request'    => $this->router->getRequestByRoute('filemanager.folder.remove', [
                    ':path' => $path
                ]),
                'title_link' => t('Delete')
            ]
        ];

        $this->core->callHook('filemanager.folder.submenu', [ &$menu ]);

        foreach ($menu as $key => &$link) {
            if (!$this->core->callHook('app.granted.route', [ $link[ 'request' ] ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = $link[ 'request' ]->getUri();
        }
        unset($link);

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
                'title_link' => t('Copy link'),
                'link'       => $this->core->getPath('files_public') . $path . '/' . $file->getFilename()
            ];
        }
        if ($this->hookUser->hookFileCopy($path, $name, $ext)) {
            $actions[] = [
                'type'       => 'button',
                'icon'       => 'fa fa-copy',
                'class'      => 'mod',
                'title_link' => t('Deplace or copy'),
                'link'       => $this->router->getRoute('filemanager.copy.admin', [
                    ':path' => $path,
                    ':name' => $name,
                    ':ext'  => '.' . $ext
                ])
            ];
        }

        return $actions;
    }

    public static function parseRecursive($dir)
    {
        $dirIterator = new \RecursiveDirectoryIterator($dir);
        $iterator    = new \RecursiveIteratorIterator($dirIterator);
        $size        = 0;
        $time        = 0;

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
