<?php

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

class HookUser
{
    public function __construct($user, $profil)
    {
        $this->user   = $user;
        $this->profil = $profil;
    }

    public function getRight($path, $user_id = null)
    {
        if (empty($user_id) && ($user = $this->user->isConnected())) {
            $user_id = $user[ 'user_id' ];
        }

        $path    = '/' . Util::cleanPath($path);
        $profils = $this->profil->getProfilsFileByUser($user_id);

        foreach ($profils as $profil) {
            $pattern = '/' . Util::cleanPath($profil[ 'folder_show' ]);
            $pattern = str_replace('{{user_id}}', $user_id, $pattern);
            $pattern .= $profil[ 'folder_show_sub' ]
                ? '.*'
                : '';
            $pattern = str_replace('/', '\/', $pattern);
            if (preg_match('/^' . $pattern . '$/', $path)) {
                return $profil;
            }
        }

        return [];
    }

    public function hookPermission(&$profil)
    {
        $profil[ 'FileManager' ] = [
            'filemanager.profil.admin' => t('Administer file profiles')
        ];
    }

    public function hookFileAdmin()
    {
        return 'filemanager.profil.admin';
    }

    public function hookFileShow($path, $name, $ext, $req = null, $user = null)
    {
        $right = $this->getRight($path, $user[ 'user_id' ]);

        if (empty($right)) {
            return false;
        }

        return $this->rightExtension($ext, $right);
    }

    public function hookFileStore($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, $user[ 'user_id' ]);

        return !empty($right[ 'file_store' ]);
    }

    public function hookFileUpdate(
        $path,
        $name,
        $ext,
        $req = null,
        $user = null
    ) {
        $right = $this->getRight($path, $user[ 'user_id' ]);
        if (empty($right[ 'file_update' ])) {
            return false;
        }

        return $this->rightExtension($ext, $right);
    }

    public function hookFileCopyClipboard(
        $path,
        $name,
        $ext,
        $user = null
    ) {
        $right = $this->getRight($path, $user[ 'user_id' ]);

        return !empty($right[ 'file_clipboard' ]);
    }

    public function hookFileDelete(
        $path,
        $name,
        $ext,
        $req = null,
        $user = null
    ) {
        $right = $this->getRight($path, $user[ 'user_id' ]);
        if (empty($right[ 'file_delete' ])) {
            return false;
        }

        return $this->rightExtension($ext, $right);
    }

    public function hookFileDownlod(
        $path,
        $name,
        $ext,
        $req = null,
        $user = null
    ) {
        $right = $this->getRight($path, $user[ 'user_id' ]);

        if (empty($right[ 'file_download' ])) {
            return false;
        }

        return $this->rightExtension($ext, $right);
    }

    public function hookFolderAdmin($path, $req = null, $user = null)
    {
        $profils = $this->profil->getProfilsFileByUser($user[ 'user_id' ]);

        return !empty($profils);
    }

    public function hookFolderShow($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, $user[ 'user_id' ]);

        return !empty($right);
    }

    public function hookFolderStore($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, $user[ 'user_id' ]);

        return !empty($right[ 'folder_store' ]) && !empty($right[ 'folder_show_sub' ]);
    }

    public function hookFolderUpdate($path, $req = null, $user = null)
    {
        $right = $this->getRight(dirname($path), $user[ 'user_id' ]);

        return !empty($right[ 'folder_update' ]) && !empty($right[ 'folder_show_sub' ]);
    }

    public function hookFolderDelete($path, $req = null, $user = null)
    {
        $right = $this->getRight(dirname($path), $user[ 'user_id' ]);

        return !empty($right[ 'folder_delete' ]) && !empty($right[ 'folder_show_sub' ]);
    }

    protected function rightExtension($ext, array $right = [])
    {
        if ($right[ 'file_extensions_all' ]) {
            return true;
        }
        if (($tmp = substr(strstr($ext, '.'), 1)) !== false) {
            $ext = $tmp;
        }

        return in_array($ext, explode(',', $right[ 'file_extensions' ]));
    }
}
