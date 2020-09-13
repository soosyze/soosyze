<?php

namespace SoosyzeCore\Template\Services;

class Block extends \Soosyze\Components\Template\Template
{
    public function getBlockWithParent($parent)
    {
        if ($block = strstr($parent, '.', true)) {
            return $this->getBlock($block)
                    ->getBlock(substr(strstr($parent, '.'), 1));
        }

        return $parent !== 'this'
            ? $this->getBlock($parent)
            : $this;
    }
}
