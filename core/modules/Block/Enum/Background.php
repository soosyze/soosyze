<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Enum;

class Background
{
    public const REPEAT = [
        'repeat'    => 'On both axes',
        'repeat-x'  => 'On horizontal axis',
        'repeat-y'  => 'On vertical axis',
        'no-repeat' => 'Do not repeat',
    ];

    public const POSITION_TOP = [
        'top left'   => 'Top left',
        'top center' => 'Top center',
        'top right'  => 'Top right',
    ];

    public const POSITION_CENTER = [
        'center left'   => 'Middle left',
        'center center' => 'Middle center',
        'center right'  => 'Middle right',
    ];

    public const POSITION_BOTTOM = [
        'bottom left'   => 'Bottom left',
        'bottom center' => 'Bottom center',
        'bottom right'  => 'Bottom right',
    ];

    public const SIZE = [
        '100%',
        'auto',
        'contain',
        'cover',
    ];

    public const POSITIONS = [
        ['label' => 'Top', 'value' => self::POSITION_TOP],
        ['label' => 'Middle', 'value' => self::POSITION_CENTER],
        ['label' => 'Bottom', 'value' => self::POSITION_BOTTOM],
    ];

    private function __construct()
    {
    }

    public static function getPositionsImplode(string $separator = ','): string
    {
        return implode(
            $separator,
            [
                implode($separator, array_keys(self::POSITION_TOP)),
                implode($separator, array_keys(self::POSITION_CENTER)),
                implode($separator, array_keys(self::POSITION_BOTTOM))
            ]
        );
    }

    public static function getRepeatImplode(string $separator = ','): string
    {
        return implode(
            $separator,
            array_keys(self::REPEAT)
        );
    }

    public static function getSizesImplode(string $separator = ','): string
    {
        return implode($separator, self::SIZE);
    }
}
