<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\BackupManager\Services;

use Core;
use Soosyze\Components\Router\Router;
use Soosyze\Config;

class BackupManager
{
    /**
     * ISO8601 adapté.
     */
    public const DATE_FORMAT = 'Y-m-d\TH-i-s';

    public const DATE_REGEX = '2[\d]{3}-(0[1-9]|1[0-2])-(0[1-9]|[12][\d]|3[01])T([01][\d]|2[0-3])-[0-5][\d]-[0-5][\d]';

    public const SUFFIX = 'soosyzecms.zip';

    /**
     * @var Core
     */
    private $core;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $repository;

    /**
     * @var string
     */
    private $root;

    /**
     * @var int|null
     */
    private $maxBackups;

    public function __construct(Core $core, Router $router, ?int $maxBackups, string $root)
    {
        $this->core       = $core;
        $this->router     = $router;
        $this->maxBackups = $maxBackups;
        $this->root       = $root;

        $this->repository = $this->core->getDir('backup_dir', '../soosyze_backups');
    }

    public function isRepository(): bool
    {
        return is_dir($this->repository);
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function listBackups(): array
    {
        if (!$this->isRepository()) {
            return [];
        }

        $backups = [];
        foreach (new \DirectoryIterator($this->repository) as $file) {
            if ($file->isDot() || $file->getExtension() !== 'zip' || !preg_match('/^' . self::DATE_REGEX . 'soosyzecms/', $file->getFilename())) {
                continue;
            }
            $backups[] = [
                'date'          => \date_create_from_format(self::DATE_FORMAT, str_replace('soosyzecms.zip', '', $file->getFilename())),
                'size'          => $file->getSize(),
                'download_link' => $this->router->generateUrl('backupmanager.download', [
                    'file' => strtr($file->getFilename(), [ self::SUFFIX => '' ])
                ]),
                'restore_link'  => $this->router->generateUrl('backupmanager.restore', [
                    'file' => strtr($file->getFilename(), [ self::SUFFIX => '' ])
                ]),
                'delete_link'   => $this->router->generateUrl('backupmanager.delete', [
                    'file' => strtr($file->getFilename(), [ self::SUFFIX => '' ])
                ])
            ];
        }
        \array_multisort($backups, \SORT_DESC);

        return $backups;
    }

    public function doBackup(): bool
    {
        if (($backup = $this->getFreshZip()) === null) {
            return false;
        }
        $backup = $this->zipRecursivly($this->root, $backup);

        return $backup->close();
    }

    public function restore(string $date): bool
    {
        $file = $this->repository . DS . $date . self::SUFFIX;
        if (file_exists($file)) {
            $zip = new \ZipArchive();
            $zip->open($file);
            $zip->extractTo($this->root);

            return $zip->close();
        }

        return false;
    }

    public function delete(string $date): bool
    {
        $file = $this->repository . DS . $date . self::SUFFIX;

        return file_exists($file) && \unlink($file);
    }

    public function deleteAll(): bool
    {
        foreach (new \DirectoryIterator($this->repository) as $file) {
            if ($file->isDot() || $file->isDir() || $file->getExtension() != 'zip') {
                continue;
            }
            \unlink($file->getPathname());
        }

        return true;
    }

    public function getBackup(string $date): ?string
    {
        if (!file_exists($file = $this->repository . DS . $date . self::SUFFIX)) {
            return null;
        }

        return ($content = \file_get_contents($file)) === false
            ? null
            : $content;
    }

    private function getFreshZip(): ?\ZipArchive
    {
        if (!$this->isRepository()) {
            return null;
        }

        $dir = scandir($this->repository, SCANDIR_SORT_ASCENDING);

        if ($this->maxBackups &&
            is_array($dir) &&
            count($dir) - 2 >= $this->maxBackups &&
            preg_match('/^' . self::DATE_REGEX . 'soosyzecms/', $dir[ 2 ])
        ) {
            \unlink($this->repository . DS . $dir[ 2 ]);
        }

        $backup = new \ZipArchive();

        if ($backup->open($this->repository . DS . $this->generateBackupName(), \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            return $backup;
        }

        return null;
    }

    private function generateBackupName(): string
    {
        return \date(self::DATE_FORMAT) . self::SUFFIX;
    }

    private function zipRecursivly(string $dir, \ZipArchive $zip): \ZipArchive
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
                $zip->addFile($file->getPathname(), str_replace($this->root, '', $file->getPathname()));
            }
        }

        return $zip;
    }
}
