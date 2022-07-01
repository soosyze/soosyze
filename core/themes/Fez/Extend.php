<?php

namespace Soosyze\Core\Themes\Fez;

class Extend extends \Soosyze\Core\Modules\System\ExtendTheme
{
    public function getDir(): string
    {
        return __DIR__;
    }

    public function boot(): void
    {
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/main.json');
    }
}
