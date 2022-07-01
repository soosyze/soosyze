<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block;

interface BlockInterface
{
    public function hookBlockCreateFormData(array &$blocks): void;
}
