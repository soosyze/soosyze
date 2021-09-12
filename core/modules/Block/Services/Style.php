<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Services;

use Core;
use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Router\Router;
use Soosyze\Components\Validator\Validator;
use Soosyze\Config;
use Soosyze\Core\Modules\Block\Model;
use Soosyze\Core\Modules\FileSystem\Services\File;

class Style
{
    public const PATH_THEME = '%s/themes/%s.css';

    /** @var Core */
    private $core;

    /** @var Config */
    private $config;

    /** @var File */
    private $file;

    /** @var Router */
    private $router;

    public function __construct(
        Core $core,
        Config $config,
        File $file,
        Router $router
    ) {
        $this->core = $core;
        $this->config = $config;
        $this->file = $file;
        $this->router = $router;
    }

    public function generateStyleCssLink(string $selector, Model\Style $style): string
    {
        $css = self::generateStyle(['color:%s;' => $style->colorLink]);

        return empty($css)
            ? ''
            : sprintf('%s a{%s}', $selector, $css) . PHP_EOL;
    }

    public function generateStyleCssTitle(string $selector, Model\Style $style): string
    {
        $css = self::generateStyle([
            'color:%s;' => $style->colorTitle,
            'font-family:"%s";' => $style->fontFamilyTitle,
        ]);

        return empty($css)
            ? ''
            : str_replace(
                ['{selector}', ':styles'],
                [$selector, $css],
                '{selector} h2, {selector} h3, {selector} h4, {selector} h5, {selector} h6{:styles}'
            ) . PHP_EOL;
    }

    public function generateStyleCssBlock(string $selector, Model\Style $style): string
    {
        $css = '';
        foreach ([
            $this->generateStyleCssBorder($style),
            $this->generateStyleCssBackground($style),
            $this->generateStyleCsSpacing($style),
            $this->generateStyleCssText($style),
        ] as $styleStr) {
            $css .= $styleStr;
        }

        return empty($css)
            ? ''
            : sprintf('%s{%s}', $selector, $css) . PHP_EOL;
    }

    public function generateStyleCssBorder(Model\Style $style): string
    {
        return self::generateStyle([
            'border-style:%spx;' => $style->borderStyle,
            'border-width:%spx;' => $style->borderWidth,
            'border-color:%s;' => $style->borderColor,
            'border-radius:%spx;' => $style->borderRadius,
        ]);
    }

    public function generateStyleCssBackground(Model\Style $style): string
    {
        return self::generateStyle([
            'margin:%spx;' => $style->margin,
            'padding:%spx;' => $style->padding,
        ]);
    }

    public function generateStyleCsSpacing(Model\Style $style): string
    {
        return self::generateStyle([
            'background-repeat:%s;' => $style->backgroundRepeat,
            'background-position:%s;' => $style->backgroundPosition,
            'background-size:%s;' => $style->backgroundSize,
            'background-image: url(\'%s\');' => empty($style->backgroundImage)
                ? null
                : $this->router->getBasePath() . $style->backgroundImage,
            'background-color:%s;' => $style->backgroundColor,
        ]);
    }

    public function generateStyleCssText(Model\Style $style): string
    {
        return self::generateStyle([
            'color:%s;' => $style->colorText,
            'font-family:"%s";' => $style->fontFamilyText,
        ]);
    }

    public function saveBackgroundImage(string $themeName, int $id, Validator $validator): void
    {
        $key = 'background_image';

        /** @phpstan-var UploadedFileInterface $uploadedFile */
        $uploadedFile = $validator->getInput($key);

        $this->file
            ->add($uploadedFile, $validator->getInputString("file-$key-name"))
            ->setName($key . $id)
            ->setPath('/theme/' . $themeName . '/blocks')
            ->isResolvePath()
            ->callGet(function (string $name) use ($themeName, $id, $key): ?string {
                $data = $this->getSettingsAssets($themeName);

                return is_string($data[$id][$key] ?? null)
                    ? $data[$id][$key]
                    : null;
            })
            ->callMove(function (string $name, \SplFileInfo $fileInfo) use ($themeName, $id, $key): void {
                $data            = $this->getSettingsAssets($themeName);
                $data[$id][$key] = $fileInfo->getPathname();

                $this->config->set("template-$themeName.assets", $data);
            })
            ->callDelete(function () use ($themeName, $id, $key): void {
                $data            = $this->getSettingsAssets($themeName);
                $data[$id][$key] = '';

                $this->config->set("template-$themeName.assets", $data);
            })
            ->save();
    }

    public function getUrlCompile(string $themeName): string
    {
        $buildUrl = $this->config->get("template-$themeName.mixe", '');

        if (!is_string($buildUrl)) {
            throw new \Exception('Build URL must be a string');
        }

        return $buildUrl;
    }

    public function getUrlStyle(string $themeName): string
    {
        $vendor = $this->core->getPath('assets_public', 'public/vendor', false);

        return sprintf(
            self::PATH_THEME,
            $vendor,
            $themeName
        );
    }

    public function getPathStyle(string $themeName): string
    {
        $vendor = $this->core->getDir('assets_public', 'public/vendor', false);

        return sprintf(
            self::PATH_THEME,
            $vendor,
            $themeName
        );
    }

    public function getSettingsBlocksStyle(string $themeName): array
    {
        /** @var array */
        return $this->config->get("template-$themeName.block", []);
    }

    public function setSettingsBlocksStyle(string $themeName, array $data): void
    {
        $this->config->set("template-$themeName.block", $data);
    }

    public function getSettingsAssets(string $themeName): array
    {
        /** @var array */
        return $this->config->get("template-$themeName.assets", []);
    }

    private static function generateStyle(array $styles): string
    {
        $css = '';
        foreach ($styles as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $css .= sprintf($key, $value);
        }

        return $css;
    }
}
