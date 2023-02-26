<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\System\Services;

use Soosyze\Config;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;
use Soosyze\Core\Modules\QueryBuilder\Services\Schema;
use Soosyze\Core\Modules\System\Contract\ConfigMigrationInterface;
use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Core\Modules\System\ExtendModule;

class Migration
{
    private const REGEX_MIGRATION_NAME = '/^2[\d]{3}_(0[1-9]|1[0-2])_(0[1-9]|[12][\d]|3[01])_\d{6}_[a-z0-9_]+/';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Schema
     */
    private $schema;

    public function __construct(
        Composer $composer,
        Config $config,
        Query $query,
        Schema $schema
    ) {
        $this->composer = $composer;
        $this->config   = $config;
        $this->query    = $query;
        $this->schema   = $schema;
    }

    /**
     * Si une migration est disponible.
     */
    public function isMigration(): bool
    {
        /** @phpstan-var array<string> $titleModulesActive */
        $titleModulesActive  = $this->query->from('module_active')->lists('title');
        /** @phpstan-var array<string> $migrationsInstalled */
        $migrationsInstalled = $this->query->from('migration')->lists('migration');
        $composers           = $this->composer->getModuleComposers();

        foreach ($titleModulesActive as $title) {
            if (!isset($composers[ $title ])) {
                continue;
            }
            /** @phpstan-var class-string<ExtendModule> $extendClass */
            $extendClass = $this->composer->getExtendClass($title, $composers);

            $dir = (new $extendClass)->getDir() . DS . 'Migrations';
            if (!\is_dir($dir)) {
                continue;
            }

            foreach (new \DirectoryIterator($dir) as $fileInfo) {
                if (
                    $fileInfo->isFile() &&
                    !in_array($fileInfo->getBasename('.php'), $migrationsInstalled) &&
                    preg_match(self::REGEX_MIGRATION_NAME, $fileInfo->getBasename('.php'))) {
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
    public function migrate(): void
    {
        /** @phpstan-var array<string> $titleModulesActive */
        $titleModulesActive  = $this->query->from('module_active')->lists('title');
        /** @phpstan-var array<string> $migrationsInstalled */
        $migrationsInstalled = $this->query->from('migration')->lists('migration');
        $composers           = $this->composer->getModuleComposers();

        $callbacks = [];
        foreach ($titleModulesActive as $title) {
            if (!isset($composers[ $title ])) {
                continue;
            }
            /** @phpstan-var class-string<ExtendModule> $extendClass */
            $extendClass = $this->composer->getExtendClass($title, $composers);

            $dir = (new $extendClass)->getDir() . DS . 'Migrations';
            if (!\is_dir($dir)) {
                continue;
            }

            foreach (new \DirectoryIterator($dir) as $fileInfo) {
                if (
                    !$fileInfo->isFile() ||
                    in_array($fileInfo->getBasename('.php'), $migrationsInstalled) ||
                    !preg_match(self::REGEX_MIGRATION_NAME, $fileInfo->getBasename('.php'))
                ) {
                    continue;
                }

                $callbacks[ $fileInfo->getBasename('.php') ] = [
                    'callback'  => include_once $fileInfo->getRealPath(),
                    'extension' => $title,
                    'migration' => $fileInfo->getBasename('.php')
                ];
            }
        }

        ksort($callbacks);
        $query = clone $this->query;
        $this->query->insertInto('migration', [ 'migration', 'extension' ]);

        foreach ($callbacks as $callback) {
            if ($callback[ 'callback' ] instanceof DatabaseMigrationInterface) {
                $callback[ 'callback' ]->up($this->schema, $query);
            }
            if ($callback[ 'callback' ] instanceof ConfigMigrationInterface) {
                $callback[ 'callback' ]->upConfig($this->config);
            }

            $query->init();
            $this->query->values([
                $callback[ 'migration' ], $callback[ 'extension' ]
            ]);
        }

        $this->query->execute();
        $this->config
            ->set('settings.module_update', false)
            ->set('settings.module_update_time', time());
    }

    /**
     * Installe les migrations d'un module.
     *
     * @param string $dir       Chemin des migrations
     * @param string $extension Titre du module
     */
    public function installMigration(string $dir, string $extension): void
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

    public function uninstallMigration(string $extension): void
    {
        $this->query
            ->delete()
            ->from('migration')
            ->where('extension', '=', $extension)
            ->execute();
    }
}
