<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Model;

use Psr\Http\Message\RequestInterface;

/**
 * @property-read string $backgroundColor
 * @property-read string $backgroundImage
 * @property-read string $backgroundPosition
 * @property-read string $backgroundRepeat
 * @property-read string $backgroundSize
 * @property-read string $borderColor
 * @property-read int $borderRadius
 * @property-read string $borderStyle
 * @property-read int $borderWidth
 * @property-read string $colorLink
 * @property-read string $colorText
 * @property-read string $colorTitle
 * @property-read string $fontFamilyText
 * @property-read string $fontFamilyTitle
 * @property-read int $margin
 * @property-read int $padding
 */
final class Style implements \JsonSerializable
{
    /** @var string */
    private $backgroundColor;

    /** @var string */
    private $backgroundImage;

    /** @var string */
    private $backgroundPosition;

    /** @var string */
    private $backgroundRepeat;

    /** @var string */
    private $backgroundSize;

    /** @var string */
    private $borderColor;

    /** @var int */
    private $borderRadius;

    /** @var string */
    private $borderStyle;

    /** @var int */
    private $borderWidth;

    /** @var string */
    private $colorLink;

    /** @var string */
    private $colorText;

    /** @var string */
    private $colorTitle;

    /** @var string */
    private $fontFamilyText;

    /** @var string */
    private $fontFamilyTitle;

    /** @var int */
    private $margin;

    /** @var int */
    private $padding;

    private function __construct(
        string $backgroundColor,
        string $backgroundImage,
        string $backgroundPosition,
        string $backgroundRepeat,
        string $backgroundSize,
        string $borderColor,
        int $borderRadius,
        string $borderStyle,
        int $borderWidth,
        string $colorLink,
        string $colorText,
        string $colorTitle,
        string $fontFamilyText,
        string $fontFamilyTitle,
        int $margin,
        int $padding
    ) {
        $this->backgroundColor = $backgroundColor;
        $this->backgroundImage = $backgroundImage;
        $this->backgroundPosition = $backgroundPosition;
        $this->backgroundRepeat = $backgroundRepeat;
        $this->backgroundSize = $backgroundSize;
        $this->borderColor = $borderColor;
        $this->borderRadius = $borderRadius;
        $this->borderStyle = $borderStyle;
        $this->borderWidth = $borderWidth;
        $this->colorLink = $colorLink;
        $this->colorText = $colorText;
        $this->colorTitle = $colorTitle;
        $this->fontFamilyText = $fontFamilyText;
        $this->fontFamilyTitle = $fontFamilyTitle;
        $this->margin = $margin;
        $this->padding = $padding;
    }

    /**
     * @return string|int
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \InvalidArgumentException($name . ' doesn\'t property');
    }

    /**
     * @return string|int
     */
    public function __isset(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \InvalidArgumentException($name . ' doesn\'t property');
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'background_color' => $this->backgroundColor,
            'background_image' => $this->backgroundImage,
            'background_position' => $this->backgroundPosition,
            'background_repeat' => $this->backgroundRepeat,
            'background_size' => $this->backgroundSize,
            'border_color' => $this->borderColor,
            'border_radius' => $this->borderRadius,
            'border_style' => $this->borderStyle,
            'border_width' => $this->borderWidth,
            'color_link' => $this->colorLink,
            'color_text' => $this->colorText,
            'color_title' => $this->colorTitle,
            'font_family_text' => $this->fontFamilyText,
            'font_family_title' => $this->fontFamilyTitle,
            'margin' => $this->margin,
            'padding' => $this->padding,
        ];
    }

    public static function createFromRequest(RequestInterface $request): self
    {
        return self::createFromArray((array) $request->getBody());
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data['background_color'] ?? '',
            $data['background_image'] ?? '',
            $data['background_position'] ?? '',
            $data['background_repeat'] ?? '',
            $data['background_size'] ?? '',
            $data['border_color'] ?? '',
            $data['border_radius'] ?? 0,
            $data['border_style,'] ?? '',
            $data['border_width'] ?? 0,
            $data['color_link'] ?? '',
            $data['color_text'] ?? '',
            $data['color_title'] ?? '',
            $data['font_family_text'] ?? '',
            $data['font_family_title'] ?? '',
            $data['margin'] ?? 0,
            $data['padding'] ?? 0
        );
    }

    public static function createFromJson(string $json): self
    {
        return self::createFromArray(
            (array) json_decode($json)
        );
    }
}
