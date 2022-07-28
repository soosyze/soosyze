<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Filter\Services;

use Soosyze\Kses\AllowedList;
use Soosyze\Kses\Xss as Kses;

class Xss extends Kses
{
    public function __construct()
    {
        parent::__construct(AllowedList::getTagsAdmin());
    }

    public function getKses(): self
    {
        $clone = clone $this;

        return $clone->setAllowedTags([]);
    }
}
