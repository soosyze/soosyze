<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Filter\Services;

use Soosyze\Config;

class Parsedown extends \Parsedown
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function filter(string $str): string
    {
        return $this->config->get('settings.node_markdown', false)
            ? $this->text($str)
            : $str;
    }
}
