<?php

namespace SoosyzeCore\FileManager\Services;

use Soosyze\Components\Util\Util;

class HookUser
{
    protected $profil;

    protected $user;

    public function __construct($profil, $user)
    {
        $this->profil = $profil;
        $this->user   = $user;
    }

    public function getRight($path, $userId = null)
    {
        if (empty($userId) && ($user = $this->user->isConnected())) {
            $userId = $user[ 'user_id' ];
        }

        $path    = $path === '/' || $path === ''
            ? '/'
            : Util::cleanPath('/' . $path);
        $profils = $this->profil->getProfilsFileByUser($userId);

        foreach ($profils as $profil) {
            $pattern = $profil[ 'folder_show' ];
            $pattern = str_replace(':user_id', $userId, $pattern);
            $pattern = preg_quote($pattern, '/');
            $pattern .= $profil[ 'folder_show_sub' ]
                ? '.*'
                : '';

            if (preg_match('/^' . $pattern . '$/', $path)) {
                return $profil;
            }
        }

        return [];
    }

    public function hookPermission(&$profil)
    {
        $profil[ 'FileManager' ] = [
            'filemanager.profil.admin' => 'Administer file permissions'
        ];
    }

    public function hookFileAdmin()
    {
        return 'filemanager.profil.admin';
    }

    public function hookFileShow($path, $name, $ext, $req = null, $user = null)
    {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        if (empty($right)) {
            return false;
        }

        return $this->rightExtension($ext, $right);
    }

    public function hookFileStore($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        return !empty($right[ 'file_store' ]);
    }

    public function hookFileUpdate(
        $path,
        $name,
        $ext,
        $req = null,
        $user = null
    ) {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        if (empty($right[ 'file_update' ])) {
            return false;
        }

        return $this->rightExtension($ext, $right);
    }

    public function hookFileCopyClipboard(
        $path,
        $name,
        $ext,
        $req = null,
        $user = null
    ) {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        return !empty($right[ 'file_clipboard' ]);
    }

    public function hookFileCopy(
        $path,
        $name,
        $ext,
        $req = null,
        $user = null
    ) {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        return !empty($right[ 'file_copy' ]);
    }

    public function hookFileDelete(
        $path,
        $name,
        $ext,
        $req = null,
        $user = null
    ) {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

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
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        if (empty($right[ 'file_download' ])) {
            return false;
        }

        return $this->rightExtension($ext, $right);
    }

    public function hookFolderAdmin($req = null, $user = null)
    {
        $profils = $this->profil->getProfilsFileByUser($user[ 'user_id' ]);

        return !empty($profils);
    }

    public function hookFolderShow($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        return !empty($right);
    }

    public function hookFolderStore($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        return !empty($right[ 'folder_store' ]) && !empty($right[ 'folder_show_sub' ]);
    }

    public function hookFolderUpdate($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        return !empty($right[ 'folder_update' ]) && !empty($right[ 'folder_show_sub' ]);
    }

    public function hookFolderDelete($path, $req = null, $user = null)
    {
        $right = $this->getRight($path, empty($user[ 'user_id' ]) ? null : $user[ 'user_id' ]);

        return !empty($right[ 'folder_delete' ]) && !empty($right[ 'folder_show_sub' ]);
    }

    public function getMaxUpload($path)
    {
        $profil = $this->getRight($path);

        if (empty($profil['file_size'])) {
            return Util::getOctetUploadLimit();
        }

        $maxUpload = $profil[ 'file_size' ] * 1048576;

        return min(Util::getOctetUploadLimit(), $maxUpload);
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
