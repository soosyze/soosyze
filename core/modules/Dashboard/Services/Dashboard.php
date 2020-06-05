<?php

namespace SoosyzeCore\Dashboard\Services;

class Dashboard
{
    protected $config;

    /**
     * @var \Core
     */
    protected $core;

    public function __construct($config, $core)
    {
        $this->config = $config;
        $this->core   = $core;
    }

    public function getSizeDatabase()
    {
        $host = $this->config->get('database.host');

        return $this->getSizeDirectoryInterator(ROOT . $host);
    }

    public function getSizeFiles()
    {
        $dir = $this->core->getDir('files_public');

        return $this->getSizeDirectoryInterator($dir);
    }

    public function getSizeBackups()
    {
        $dir = $this->core->getDir('backup_dir');

        return $this->getSizeDirectoryInterator($dir);
    }

    public function getSizeDirectoryInterator($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            $dirIterator = new \RecursiveDirectoryIterator($dir);
            $iterator    = new \RecursiveIteratorIterator($dirIterator);

            $iterator->rewind();
            foreach ($iterator as $file) {
                if ($iterator->isDot() || $iterator->isLink()) {
                    continue;
                }
                $size += $file->getSize();
            }
        }

        return $size;
    }
}
