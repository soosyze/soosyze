<?php

declare(strict_types=1);

namespace SoosyzeCore\Filter\Services;

class Filter
{
    /**
     * @var array
     */
    private $filters;

    public function __construct(
        LazyLoding $lazyLoding,
        Parsedown $parsdown,
        Xss $xss
    ) {
        $this->filters = [
            $xss, $parsdown, $lazyLoding
        ];
    }

    public function parse(string $str): string
    {
        foreach ($this->filters as $filter) {
            $str = $filter->filter($str);
        }

        return $str;
    }
}
