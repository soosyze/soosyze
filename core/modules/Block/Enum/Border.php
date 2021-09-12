<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Enum;

final class Border
{
    public const STYLE = [
        'dashed',
        'double',
        'dotted',
        'groove',
        'hidden',
        'inset',
        'none',
        'outset',
        'ridge',
        'solid',
    ];

    private function __construct()
    {
    }

    public static function getStylesImplode(string $separator = ','): string
    {
        return implode(',', self::STYLE);
    }
}
