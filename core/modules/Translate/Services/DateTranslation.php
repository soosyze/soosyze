<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Translate\Services;

class DateTranslation
{
    private const REPLACE_KEYS = [
        'months',
        'months_short',
        'weekdays',
        'weekdays_short',
        'weekdays_min',
    ];

    /**
     * @var array<string, string>
     */
    private $translates = [];

    /**
     * @var array<string, string>
     */
    private $defaultTranslation = [];

    public function __construct(string $dir, ?string $langDefault = null)
    {
        $translatesFile         = sprintf('%s/%s.php', $dir, $langDefault ?? 'en');
        $defaultTranslationFile = sprintf('%s/%s.php', $dir, 'en');

        if (is_file($translatesFile)) {
            $this->translates = require $translatesFile;
        }
        if ($langDefault === 'en') {
            $this->defaultTranslation = $this->translates;
        } elseif (is_file($defaultTranslationFile)) {
            $this->defaultTranslation = require $defaultTranslationFile;
        }
    }

    public function date(string $format, int $time): string
    {
        $date = date($format, $time);

        foreach (self::REPLACE_KEYS as $replaceKey) {
            $date = str_replace(
                $this->defaultTranslation[ $replaceKey ],
                $this->translates[ $replaceKey ],
                $date
            );
        }

        return $date;
    }
}
