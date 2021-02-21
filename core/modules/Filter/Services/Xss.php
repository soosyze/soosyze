<?php

namespace SoosyzeCore\Filter\Services;

use Kses\Kses;
use Kses\KsesAllowedList;

class Xss extends Kses
{
    public function __construct()
    {
        parent::__construct(KsesAllowedList::getTagsAdmin());
    }
}
