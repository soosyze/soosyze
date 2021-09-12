<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Enum;

final class Font
{
    public const SANS_SERIF = [
        'Arial',
        'Calibri',
        'Helvetica',
        'Lucida Sans',
        'Open Sans',
        'Verdana',
    ];

    public const SERIF = [
        'DejaVu Serif',
        'FreeSerif',
        'Georgia',
        'Liberation Serif',
        'Norasi',
        'Times New Roman',
        'Times',
    ];

    public const FANTASY = [
        'Herculanum',
        'Impact',
        'Papyrus',
    ];

    public const CURSIVE = [
        'Lucida Calligraphy'
    ];

    public const MONOSPACE = [
        'Consolas',
        'DejaVu Sans Mono',
        'Lucida Console',
    ];

    public const FONTS = [
        ['label' => 'Sans-serif', 'value' => self::SANS_SERIF],
        ['label' => 'serif', 'value' => self::SERIF],
        ['label' => 'Fantasy', 'value' => self::FANTASY],
        ['label' => 'Cursive', 'value' => self::CURSIVE],
        ['label' => 'Monospace', 'value' => self::MONOSPACE],
    ];

    private function __construct()
    {
    }

    public static function getImplode(string $separator = ','): string
    {
        return implode(
            $separator,
            [
                implode($separator, self::CURSIVE),
                implode($separator, self::FANTASY),
                implode($separator, self::MONOSPACE),
                implode($separator, self::SANS_SERIF),
                implode($separator, self::SERIF)
            ]
        );
    }
}
