<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SoosyzeCore\FileManager\Services;

/**
 * Description of FileManagerIterator
 *
 * @author mnoel
 */
class FilterManagerIterator extends \FilterIterator
{
    /**
     * @var \SoosyzeCore\FileManager\Services\HookUser
     */
    protected $hookUser;

    public function __construct($hookUser)
    {
        $this->hookUser = $hookUser;
    }

    public function load($path, \Iterator $iterator)
    {
        parent::__construct($iterator);
        $this->path = $path;

        return $this;
    }

    public function accept()
    {
        $file   = $this->current();
        $accept = true;
        if ($file->isDot() || $file->isLink()) {
            $accept = false;
        } elseif ($file->isFile()) {
            $name = '/' . $file->getBasename('.' . $file->getExtension());
            $ext  = $file->getExtension();

            if (!in_array($ext, FileManager::getWhiteList())) {
                $accept = false;
            } elseif ($file->getBasename() === '.' . $file->getExtension()) {
                $accept = false;
            } elseif (!$this->hookUser->hookFileShow($this->path, $name, $ext)) {
                $accept = false;
            }
        } elseif ($file->isDir() && !$this->hookUser->hookFolderShow($this->path . $file->getBasename())) {
            $accept = false;
        }

        return $accept;
    }
}
