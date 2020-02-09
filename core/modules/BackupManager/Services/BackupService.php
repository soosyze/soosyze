<?php

namespace SoosyzeCore\BackupManager\Services;

class BackupService
{
    /**
     * ISO8601 adapté.
     */
    const DATE_FORMAT = 'Y-m-d\TH-i-s';
    
    const DATE_REGEX = '2[\d]{3}-(0[1-9]|1[0-2])-(0[1-9]|[12][\d]|3[01])T([01][\d]|2[0-3])-[0-5][\d]-[0-5][\d]';
    
    const SUFFIX = 'soosyzecms.zip';
    
    protected $core;
    
    protected $router;
    
    protected $config;

    private $repository;
    
    public function __construct(\Soosyze\App $core, \Soosyze\Components\Router\Router $router, $config)
    {
        $this->core = $core;
        $this->router = $router;
        $this->config = $config;
      
        $this->repository = $this->core->getDir('backup_dir', '../soosyze_backups');
    }
    
    public function listBackups()
    {
        $backups = [];
        foreach (new \DirectoryIterator($this->repository) as $file) {
            if ($file->isDot() || $file->getExtension() !== 'zip' || !preg_match('/^' . self::DATE_REGEX . 'soosyzecms/', $file->getFilename())) {
                continue;
            }
            $backups[] = [
                'date' => \date_create_from_format(self::DATE_FORMAT, str_replace('soosyzecms.zip', '', $file->getFilename())),
                'size' => $file->getSize(),
                'download_link' => $this->router->getRoute('backupmanager.download', [':file' => str_replace(self::SUFFIX, '', $file->getFilename())]),
                'restore_link' => $this->router->getRoute('backupmanager.restore', [':file' => str_replace(self::SUFFIX, '', $file->getFilename())]),
                'delete_link' => $this->router->getRoute('backupmanager.delete', [':file' => str_replace(self::SUFFIX, '', $file->getFilename())])
            ];
        }
        \array_multisort($backups, \SORT_DESC);
        
        return $backups;
    }
    
    public function doBackup()
    {
        $backup = $this->getFreshZip();
        if (!$backup) {
            return false;
        }
        
        $backup = $this->zipRecursivly($this->core->getSetting('root'), $backup);

        return $backup->close();
    }
    
    public function restore($date)
    {
        $file = $this->repository . DS . $date . self::SUFFIX;
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
        $file = $this->repository . DS . $date . self::SUFFIX;
        if (file_exists($file)) {
            return \unlink($file);
        }
        
        return false;
    }
    
    public function deleteAll()
    {
        foreach (new \DirectoryIterator($this->repository) as $file) {
            if ($file->isDot() || $file->isDir() || $file->getExtension() != 'zip') {
                continue;
            }
            \unlink($file->getPathname());
        }
        
        return true;
    }
    
    public function getBackup($date)
    {
        $file = $this->repository . DS . $date . self::SUFFIX;
        if (file_exists($file)) {
            return \file_get_contents($file);
        }
        
        return false;
    }
    
    private function getFreshZip()
    {
        $max_backups = $this->config->get('settings.max_backups');
        $dir = scandir($this->repository, SCANDIR_SORT_ASCENDING);
        if ($max_backups && count($dir)-2 >= $max_backups) {
            if (preg_match('/^' . self::DATE_REGEX . 'soosyzecms/', $dir[2])) {
                \unlink($this->repository . DS . $dir[2]);
            }
        }
        $backup = new \ZipArchive();
        if ($backup->open($this->repository . DS . $this->generateBackupName(), \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            return $backup;
        }
        
        return false;
    }
    
    private function generateBackupName()
    {
        return \date(self::DATE_FORMAT) . self::SUFFIX;
    }
    
    private function zipRecursivly($dir, \ZipArchive $zip)
    {
        $dit = new \DirectoryIterator($dir);
        foreach ($dit as $file) {
            if ($file->isDot() || $file->getPath() === $this->repository) {
                continue;
            }
            if ($file->isDir()) {
                /* Ne prend pas en compte les dossiers comme .git */
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
