<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Template\Services;

use Soosyze\Components\Template\Template;

class Block extends Template
{
    public function getBlockWithParent(string $selector): Block
    {
        sscanf($selector, '%[a-z].%s', $parent, $child);

        if ($child) {
            /** @phpstan-ignore-next-line */
            return $this
                    ->getBlock((string) $parent)
                    ->getBlock((string) $child);
        }

        /** @phpstan-ignore-next-line */
        return $selector !== 'this'
            ? $this->getBlock($selector)
            : $this;
    }
}
