<?php

namespace SoosyzeCore\Template\Services;

class TemplateHtml extends \Soosyze\Components\Template\Template
{
    /**
     * Le nom du fichier template par défaut.
     *
     * @var string
     */
    protected $nameDefault = '';

    /**
     * Les noms des fichiers de template pouvant être utilisé à la place du fichier par défaut.
     *
     * @var string[]
     */
    protected $nameOverride = [];

    public function getBlockWithParent($parent)
    {
        if (($block = strstr($parent, '.', true))) {
            return $this->getBlock($block)
                    ->getBlock(substr(strstr($parent, '.'), 1));
        }

        return $parent !== 'this'
            ? $this->getBlock($parent)
            : $this;
    }

    public function addNameOverride($name)
    {
        if ($this->nameDefault === '') {
            $this->nameDefault = $this->name;
        }
        $this->nameOverride[] = $name;

        return $this;
    }

    public function addNamesOverride(array $names)
    {
        foreach ($names as $name) {
            $this->addNameOverride($name);
        }

        return $this;
    }

    public function getNameOverride()
    {
        return $this->nameOverride;
    }

    public function getNameDefault()
    {
        return $this->nameDefault;
    }
}
