<?php

namespace SoosyzeCore\BackupManager\Services;

class BackupService
{
    protected $core;
    
    private $repository;
    
    public function __construct(\Soosyze\App $core)
    {
        $this->core = $core;
      
        $this->repository = $this->core->getSetting('backup_dir');
        if (!file_exists($this->repository)) {
            \mkdir($this->repository);
        }
    }
    
    public function listBackups()
    {
        foreach (new \DirectoryIterator($this->repository) as $file) {
            if ($file->isDot()) {
                continue;
            }
            $backups[] = [
                'date' => \date_create_from_format('Ymd-His', str_replace('soosyzecms.zip', '', $file->getFilename())),
                'size' => $file->getSize(),
                'restore_link' => '/?q=admin/backupmanager/restore/' . str_replace('soosyzecms.zip', '', $file->getFilename()),
                'delete_link' => '/?q=admin/backupmanager/delete/' . str_replace('soosyzecms.zip', '', $file->getFilename())
            ];
        }
        \array_multisort($backups, \SORT_DESC);
        
        return $backups;
    }
    
    public function doBackup()
    {
        $backup = new \ZipArchive();
        if (!$backup->open($this->repository . DS . $this->generateBackupName(), \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            return false;
        }
        
        $backup = $this->zipRecursivly($this->core->getSetting('root'), $backup);

        return $backup->close();
    }
    
    public function restore($date)
    {
        $file = $this->repository . DS . $date . 'soosyzecms.zip';
        if (file_exists($file)) {
            $zip = new \ZipArchive();
            $zip->open($file);
            $zip->extractTo($this->core->getSetting('root'));

            return $zip->close();
        }

        return false;
    }
    
    public function delete($date)
    {
        $file = $this->repository . DS . $date . 'soosyzecms.zip';
        if (file_exists($file)) {
            return \unlink($file);
        }
        
        return false;
    }
    
    private function generateBackupName()
    {
        return date('Ymd-His') . 'soosyzecms.zip';
    }
    
    private function zipRecursivly($dir, \ZipArchive $zip)
    {
        $dit = new \DirectoryIterator($dir);
        foreach ($dit as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isDir()) {
                //ne prend pas en compte les dossiers comme .git
                if (empty($file->getExtension())) {
                    $zip = $this->zipRecursivly($file->getPathname(), $zip);
                }
            } else {
                $zip->addFile($file->getPathname(), str_replace($this->core->getSetting('root'), '', $file->getPathname()));
            }
        }
        
        return $zip;
    }
}
