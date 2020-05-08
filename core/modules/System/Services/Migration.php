<?php

namespace SoosyzeCore\System\Services;

class Migration
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var \Soosyze\Config
     */
    protected $config;

    /**
     * @var \SoosyzeCore\QueryBuilder\Services\Query
     */
    protected $query;

    /**
     * @var \SoosyzeCore\QueryBuilder\Services\Schema
     */
    protected $schema;

    public function __construct($composer, $config, $query, $schema)
    {
        $this->composer = $composer;
        $this->config   = $config;
        $this->query    = $query;
        $this->schema   = $schema;
    }

    /**
     * Si une migration est disponible.
     *
     * @return bool
     */
    public function isMigration()
    {
        $titleModulesActive  = $this->query->from('module_active')->lists('title');
        $migrationsInstalled = $this->query->from('migration')->lists('migration');
        $allComposer         = $this->composer->getAllComposer();

        foreach ($titleModulesActive as $titleModule) {
            if (!isset($allComposer[ $titleModule ])) {
                continue;
            }

            $installer = $this->composer->getNamespace($titleModule) . 'Installer';
            $dir       = (new $installer)->getDir() . DS . 'Migrations';
            if (!\is_dir($dir)) {
                continue;
            }

            foreach (new \DirectoryIterator($dir) as $fileInfo) {
                if (
                    $fileInfo->isFile() &&
                    !in_array($fileInfo->getBasename('.php'), $migrationsInstalled)) {
                    return true;
                }
            }
        }
        $this->config->set('settings.module_update_time', time());

        return false;
    }

    /**
     * Installe les migrations non installé des modules actifs.
     */
    public function migrate()
    {
        $titleModulesActive  = $this->query->from('module_active')->lists('title');
        $migrationsInstalled = $this->query->from('migration')->lists('migration');
        $allComposer         = $this->composer->getAllComposer();

        $callbacks = [];
        foreach ($titleModulesActive as $titleModule) {
            if (!isset($allComposer[ $titleModule ])) {
                continue;
            }
            $installer = $this->composer->getNamespace($titleModule) . 'Installer';
            $dir       = (new $installer)->getDir() . DS . 'Migrations';
            if (!\is_dir($dir)) {
                continue;
            }
            foreach (new \DirectoryIterator($dir) as $fileInfo) {
                if (
                    !$fileInfo->isFile() ||
                    !preg_match('/^2[\d]{3}_(0[1-9]|1[0-2])_(0[1-9]|[12][\d]|3[01])_\d{6}_[a-z0-9_]+/', $fileInfo->getBasename('.php')) ||
                    in_array($fileInfo->getBasename('.php'), $migrationsInstalled)) {
                    continue;
                }

                $callbacks[ $fileInfo->getBasename('.php') ] = [
                    'migration' => $fileInfo->getBasename('.php'),
                    'extension' => $titleModule,
                    'callback'  => include_once $fileInfo->getRealPath()
                ];
            }
        }

        ksort($callbacks);
        $query = clone $this->query;
        $this->query->insertInto('migration', [ 'migration', 'extension' ]);

        foreach ($callbacks as $callback) {
            call_user_func_array(
                $callback[ 'callback' ][ 'up' ],
                [ $this->schema, $query ]
            );
            $this->query->values([
                $callback[ 'migration' ], $callback[ 'extension' ]
            ]);
        }

        $this->query->execute();
        $this->config->set('settings.module_update', false);
        $this->config->set('settings.module_update_time', time());
    }

    /**
     * Installe les migrations d'un module.
     *
     * @param string $dir       Chemin des migrations
     * @param string $extension Titre du module
     *
     * @return void
     */
    public function installMigration($dir, $extension)
    {
        if (!\is_dir($dir)) {
            return;
        }

        $this->query->insertInto('migration', [ 'migration', 'extension' ]);

        foreach (new \DirectoryIterator($dir) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $this->query->values([ $fileInfo->getBasename('.php'), $extension ]);
        }

        $this->query->execute();
    }

    public function uninstallMigration($extension)
    {
        $this->query
            ->delete()
            ->from('migration')
            ->where('extension', $extension)
            ->execute();
    }
}
