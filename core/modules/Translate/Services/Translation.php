<?php

namespace SoosyzeCore\Translate\Services;

class Translation extends \Soosyze\Config
{
    protected $lang;

    /**
     * @var \Soosyze\App
     */
    protected $core;

    public function __construct($core, $dir, $langDefault = 'en')
    {
        $this->core = $core;
        $this->lang = $langDefault;
        parent::__construct($dir);
    }

    public function t($str, array $vars = [])
    {
        $subject = $this->get($str, $str);
        $out     = str_replace(array_keys($vars), $vars, $subject);

        return htmlspecialchars($out);
    }

    protected function prepareKey($strKey)
    {
        if (!is_string($strKey) || $strKey === '') {
            throw new \InvalidArgumentException('The key must be a non-empty string.');
        }

        return [ $this->lang, $strKey ];
    }
}
