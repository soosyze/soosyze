<?php

declare(strict_types=1);

namespace SoosyzeCore\Filter\Services;

use Kses\Kses;
use Kses\KsesAllowedList;

class Xss extends Kses
{
    public function __construct()
    {
        parent::__construct(KsesAllowedList::getTagsAdmin());
    }

    public function getKses(): self
    {
        $clone = clone $this;

        return $clone->setAllowedTags([]);
    }
}
