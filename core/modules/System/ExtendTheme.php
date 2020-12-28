<?php

namespace SoosyzeCore\System;

abstract class ExtendTheme
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
}
