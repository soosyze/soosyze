<?php

namespace SoosyzeCore\System;

use Psr\Container\ContainerInterface;

abstract class ExtendModule
{
    private $translations = [];

    public function getTranslations()
    {
        return $this->translations;
    }

    public function loadTranslation($lang, $file)
    {
        $this->translations[ $lang ][] = $file;
    }

    abstract public function getDir();

    /**
     * Chargement des Assets du module.
     */
    abstract public function boot();

    /**
     * Script d'installation du module.
     */
    abstract public function install(ContainerInterface $ci);

    /**
     * Ajoute des données de demo.
     */
    abstract public function seeders(ContainerInterface $ci);

    /**
     * Script de dés-installation.
     */
    abstract public function uninstall(ContainerInterface $ci);

    /**
     * Install ou créer des données si d'autre modules sont présent.
     */
    abstract public function hookInstall(ContainerInterface $ci);

    /**
     * Désinstall ou supprime des données si d'autre modules se déhinstall.
     */
    abstract public function hookUninstall(ContainerInterface $ci);
}
