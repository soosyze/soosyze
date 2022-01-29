<?php

declare(strict_types=1);

namespace SoosyzeCore\Filter\Services;

class LazyLoding
{
    private const LAZY_SELECTOR = 'lazy';

    private const PATTERN_TAG = '#<\s*(?P<tag>video|img|iframe) [^\>]*>#';

    private const PATTERN_ATTR = '#(?P<attr>(?P<name>[a-z\-]*)\s*=\s*[\"|\'](?P<value>.*?)[\"|\'])+#';

    public function filter(string $str): string
    {
        return preg_replace_callback(self::PATTERN_TAG, function (array $matches): string {
            return $this->replaceSrc($matches);
        }, $str) ?? '';
    }

    private function replaceSrc(array $matches): string
    {
        $out = [];
        preg_match_all(
            self::PATTERN_ATTR,
            $matches[ 0 ],
            $out
        );

        if (!in_array('class', $out[ 'name' ])) {
            $out[ 'name' ][]  = 'class';
            $out[ 'value' ][] = '';
        }
        foreach ($out[ 'name' ] as $key => $name) {
            if ($name === 'src') {
                $out[ 'attr' ][ $key ] = sprintf('data-src="%s"', $out[ 'value' ][ $key ]);
            } elseif ($name === 'class' && strpos($out[ 'value' ][ $key ], self::LAZY_SELECTOR) === false) {
                $out[ 'attr' ][ $key ] = sprintf('class="%s %s"', $out[ 'value' ][ $key ], self::LAZY_SELECTOR);
            }
        }

        return sprintf('<%s %s>', $matches[ 'tag' ], implode(' ', $out[ 'attr' ]));
    }
}
